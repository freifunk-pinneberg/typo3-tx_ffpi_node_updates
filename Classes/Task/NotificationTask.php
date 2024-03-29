<?php

/***
 *
 * This file is part of the "Freifunk knoten Benachrichtigung" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2016 Kevin Quiatkowski <kevin@pinneberg.freifunk.net>
 *
 ***/

namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Model\Dto\AboRemoveDemand;
use FFPI\FfpiNodeUpdates\Domain\Model\Node;
use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use FFPI\FfpiNodeUpdates\Domain\Model\Abo;
use FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository;
use FFPI\FfpiNodeUpdates\Utility\MailUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class NotificationTask extends AbstractTask
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var int
     */
    public $storagePid;

    /**
     * @var int
     */
    public $unsubscribePid;

    /**
     * @var NodeRepository
     */
    protected $internalNodeRepository;

    /**
     * @var AboRepository
     */
    protected $aboRepository;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var DataMapFactory
     */
    protected $dataMapFactory;

    public function execute()
    {
        $this->initialiseMainTask();
        return $this->mainTask();
    }

    protected function initialiseMainTask(): void
    {
        /**
         * the default Extbase Object Manager
         *
         * @var ObjectManager
         */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /**
         * Builds URI for Frontend or Backend
         *
         * @var UriBuilder
         */
        $this->uriBuilder = $this->objectManager->get(UriBuilder::class);

        /**
         * DataMapFacotry, not directly used by this task, but needs to be aviable for the repository
         *
         * @var DataMapFactory
         */
        $this->dataMapFactory = $this->objectManager->get(DataMapFactory::class);

        /**
         * Saves the Repository objects into the Database
         *
         * @var PersistenceManager
         */
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);

        /**
         * Our Repository for the Freifunk Nodes
         *
         * @var NodeRepository
         */
        $this->internalNodeRepository = $this->objectManager->get(NodeRepository::class);

        /**
         * Our Repository for the Abos
         *
         * @var AboRepository
         */
        $this->aboRepository = $this->objectManager->get(AboRepository::class);

        /**
         * @var Typo3QuerySettings $querySettings
         */
        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $querySettings->setStoragePageIds([(int)$this->storagePid]);
        $querySettings->setRespectStoragePage(FALSE);
        $querySettings->setRespectSysLanguage(FALSE);
        //Set the settings for our repositorys
        $this->internalNodeRepository->setDefaultQuerySettings($querySettings);
        $this->aboRepository->setDefaultQuerySettings($querySettings);
    }

    /**
     * This method returns the destination pid as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation(): string
    {
        $string = 'Storage Page: ' . $this->storagePid . "\n";
        $string .= 'Unsubscribe Page: ' . $this->unsubscribePid . "\n";
        $string .= 'nodes.json: ' . $this->path . "\n";

        return $string;
    }

    /**
     * Execute the Task
     * The main function, will be executed by the scheduler each time
     *
     * @return bool
     */
    protected function mainTask()
    {
        /**
         * @var boolean $hasError
         */
        $hasError = false;

        // We need the External Nodes. (They come from the json file)
        $externalNodes = $this->getExternalNodes();
        if (empty($externalNodes)) {
            return false;
        }

        // And the nodes that we already have
        $internalNodes = $this->internalNodeRepository->findAll();

        // Update Every Node and Send Notifications
        if (!$this->updateAllNodes($externalNodes, $internalNodes)) {
            $hasError = true;
        }

        //Last step, Save all updated nodes to the database
        $this->persistenceManager->persistAll();

        if ($hasError) {
            return false;
        }
        return true;
    }

    /**
     * Send E-Mail notifications
     *
     * @param Node $internalNode
     * @return boolean
     */
    protected function sendNotification(Node $internalNode)
    {
        $hasError = false;

        //Get all abos for this Node
        /** @var Abo[] $abos */
        $abos = $this->aboRepository->findByNode($internalNode)->toArray();

        foreach ($abos as $abo) {

            //first, check if the abo is confirmed, we don't want to spam
            if (!$abo->getConfirmed()) {
                //Not confirmed, go to the next abo
                continue;
            }
            if (!$this->sendNotificationMail($abo, $internalNode)) {
                $hasError = true;
            }
        }
        return !$hasError;
    }

    protected function sendNotificationMail(Abo $abo, Node $internalNode): bool
    {
        //build url to remove the abo
        $url = $this->buildUnsubscribeLink($abo);

        $emailData = [
            'node' => $internalNode,
            'url' => $url
        ];

        $mail = new MailUtility();
        $send = $mail->sendMail($abo->getEmail(), 'Freifunk Pinneberg: Knoten Benachrichtigung', 'Mail/Notification.html', $emailData);

        if ($send < 1) {
            $this->scheduler->log('Mail could not be send: ' . $abo->getEmail(), 1);
            $hasError = true;
        } else {
            //Update last notification
            $abo->setLastNotification(new \DateTime());
            $this->aboRepository->update($abo);
        }
        if ($hasError) {
            return false;
        }
        return true;
    }

    protected function updateAllNodes(array $externalNodes, QueryResultInterface $internalNodes): bool
    {
        $hasError = false;
        foreach ($internalNodes as $internalNode) {
            /** @var array $externalNode */
            $externalNode = $externalNodes[$internalNode->getNodeId()];

            if ($externalNode === null) {
                continue;
            }

            if ($internalNode->getOnline() === true && $externalNode['status']['online'] === false) {
                //node changed from online to offline since last check
                $this->scheduler->log('Node ' . $internalNode->getNodeId() . ' is now offline', 0);
                $this->updateNode($internalNode, $externalNode);
                if (!$this->sendNotification($internalNode)) {
                    //it was not possible to send a notification
                    $this->scheduler->log('One or more Notifications for ' . $internalNode->getNodeId() . ' could not be send.', 1);
                    $hasError = true;
                }
            } elseif ($internalNode->getOnline() !== $externalNode['status']['online']) {
                //The status has been changed, update the object
                $this->updateNode($internalNode, $externalNode);
                //Log the change
                if ($externalNode['status']['online']) {
                    $this->scheduler->log('Node ' . $internalNode->getNodeId() . ' is now online.', 0);
                } else {
                    $this->scheduler->log('Node ' . $internalNode->getNodeId() . ' is now offline.', 0);
                }
            }
        }
        return !$hasError;
    }

    /**
     * Update Node Status
     *
     * @param Node $internalNode
     * @param array $externalNode
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function updateNode(?Node $internalNode, ?array $externalNode)
    {
        if (empty($externalNode)) {
            return;
        }
        if (!($internalNode instanceof Node)) {
            $internalNode = $this->createNodeIfNotExist($externalNode);
        }
        if (!($internalNode instanceof Node)) {
            return;
        }
        $internalNode->setOnline($externalNode['status']['online']);
        if (!empty($externalNode['name'])) {
            $internalNode->setNodeName($externalNode['name']);
        }
        if (!empty($externalNode['role'])) {
            $internalNode->setRole($externalNode['role']);
        }
        if ($internalNode->_isDirty()) {
            $internalNode->setLastChange(new \DateTime());
            $this->internalNodeRepository->update($internalNode);
        }
    }

    /**
     * @param array $externalNode
     * @return Node|null
     */
    protected function createNodeIfNotExist(array $externalNode): ?Node
    {
        if (empty($externalNode['id'])) {
            return null;
        }
        $internalNode = $this->internalNodeRepository->findOneByNodeId($externalNode['id']);
        if ($internalNode instanceof Node) {
            return $internalNode;
        }
        //Node not exists
        $node = new Node();
        $node->setNodeId($externalNode['id']);
        $node->setNodeName($externalNode['name']);
        $node->setRole($externalNode['role']);
        $node->setLastChange(new \DateTime());
        if ($externalNode['status']['online'] == true) {
            $node->setOnline(true);
        } else {
            $node->setOnline(false);
        }
        $this->internalNodeRepository->add($node);
        return $node;
    }

    /**
     * Gets External Nodes
     *
     * @return array
     */
    protected function getExternalNodes()
    {
        $json = $this->getJson();
        $nodes = json_decode($json, true);
        if ($nodes == NULL) {
            $this->scheduler->log(json_last_error_msg(), 1);
        }
        $externalNodes = $nodes['nodes'];

        if (empty($externalNodes) || !is_array($externalNodes)) {
            //We can't do anything when we don't have the exernal nodes
            $this->scheduler->log('External Nodes are empty', 1);
            return [];
        } else {
            $externalNodesNew = [];
            foreach ($externalNodes as $externalNode) {
                //the node id must be the array key
                $externalNodesNew[$externalNode['id']] = $externalNode;
            }
            $externalNodes = $externalNodesNew;
        }
        return $externalNodes;
    }

    /**
     * Gets the JSON
     *
     * @return string
     */
    protected function getJson()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //Useragent
        curl_setopt($curl, CURLOPT_USERAGENT, 'TYPO3 at ' . $_SERVER['HTTP_HOST']);
        //follow 301 and 302
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);
        $responseStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($errno = curl_errno($curl)) {
            $this->scheduler->log('Curl Error: ' . $errno . ' HTTP:' . $responseStatusCode, 1);
        }
        $body = $response;
        curl_close($curl);

        return $body;
    }

    /**
     * @param Abo $abo
     * @return string
     */
    protected function buildUnsubscribeLink(Abo $abo): string
    {
        $pid = $this->unsubscribePid;

        $aboRemoveDemand = new AboRemoveDemand();
        $aboRemoveDemand->setEmail($abo->getEmail());
        $aboRemoveDemand->setSecret($abo->getSecret());

        $url = $this->uriBuilder;
        $url->initializeObject();
        $url->reset();
        $url->uriFor(
            'removeForm',
            [
                'aboRemoveDemand' => [
                    'email' => $abo->getEmail(),
                    'secret' => $abo->getSecret()
                ]
            ],
            'Abo',
            'ffpinodeupdates',
            'Nodeabo'
        );
        $url->setTargetPageUid($pid);
        $url->setCreateAbsoluteUri(true);
        $url = $url->buildFrontendUri();
        return $url;
    }
}
