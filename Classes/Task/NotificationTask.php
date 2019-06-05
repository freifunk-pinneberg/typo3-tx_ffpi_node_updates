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

use FFPI\FfpiNodeUpdates\Domain\Model\Node;
use FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository;
use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use FFPI\FfpiNodeUpdates\Domain\Model\Abo;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Scheduler\Task;

class NotificationTask extends Task
{
    protected $constructDone = false;

    /**
     * @var string
     */
    protected $path = 'http://meshviewer.pinneberg.freifunk.net/data/nodelist.json'; //@todo get from TypoScript

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
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @param NodeRepository $nodeRepository
     */
    public function injectNodeRepository(NodeRepository $nodeRepository){
        $this->internalNodeRepository = $nodeRepository;
    }

    /**
     * NotificationTask constructor.
     */
    public function __construct()
    {
        parent::__construct();

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
        $querySettings->setRespectStoragePage(FALSE);
        $querySettings->setRespectSysLanguage(FALSE);
        //Set the settings for our repositorys
        $this->internalNodeRepository->setDefaultQuerySettings($querySettings);
        $this->aboRepository->setDefaultQuerySettings($querySettings);

        $this->constructDone = true;
    }

    /**
     * Execute the Task
     * The main function, will be executed by the scheduler each time
     *
     * @return bool
     */
    public function execute()
    {
        if ($this->constructDone !== true) {
            //I don't know why, but in test with TYPO3 7.6.14 an scheduler 7.6.0 the __construct is not automatic called
            $this->__construct();
        }

        /**
         * @var boolean $hasError
         */
        $hasError = false;

        //We need the External Nodes. (They come from the json file)
        $externalNodes = $this->getExternalNodes();

        /** @var Node[] $internalNodes */
        $internalNodes = $this->internalNodeRepository->findAll();

        foreach ($internalNodes as $internalNode) {
            /** @var array $externalNode */
            $externalNode = $externalNodes[$internalNode->getNodeId()];

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
        //Last step, Save all updated nodes to the database
        $this->persistenceManager->persistAll();

        if ($hasError) {
            return false;
        }
        return true;
    }

    /**
     * Gets the JSON
     *
     * @return string
     */
    private function getJson()
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
     * Gets External Nodes
     *
     * @return array
     */
    private function getExternalNodes()
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
            $externalNodesNew = array();
            foreach ($externalNodes as $externalNode) {
                //the node id must be the array key
                $externalNodesNew[$externalNode['id']] = $externalNode;
            }
            $externalNodes = $externalNodesNew;
        }
        return $externalNodes;
    }

    /**
     * Send E-Mail notifications
     *
     * @param Node $internalNode
     * @return boolean
     */
    private function sendNotification($internalNode)
    {
        $hasError = false;

        //Get all abos for this Node
        /** @var Abo[] $abos */
        $abos = $this->aboRepository->findByNode($internalNode)->toArray();

        //if we have only one abo, it is not an array
        if ($abos instanceof Abo) {
            $abos = array($abos);
        }
        foreach ($abos as $abo) {

            //first, check if the abo is confirmed, we don't want to spam
            if (!$abo->getConfirmed()) {
                //Not confirmed, go to the next abo
                continue;
            }

            //build url to remove the abo
            $url = '';

            //uribuilder dose not work in tasks. @todo find out why

            $pid = 1;
            $urlAttributes = array();
            $urlAttributes['tx_ffpinodeupdates_nodeabo[action]'] = 'removeForm';
            $urlAttributes['tx_ffpinodeupdates_nodeabo[controller]'] = 'Abo';
            $urlAttributes['tx_ffpinodeupdates_nodeabo[email]'] = $abo->getEmail();
            $urlAttributes['tx_ffpinodeupdates_nodeabo[secret]'] = $abo->getSecret();
            $url = $this->uriBuilder;
            $url->initializeObject();
            $url->reset();
            $url->setTargetPageUid($pid);
            $url->setCreateAbsoluteUri(true);
            $url->setArguments($urlAttributes);
            $url = $url->buildFrontendUri();


            //Create the e-mail
            //@todo make it multilingual
            //@todo use fluid templates
            /**
             * @var MailMessage $mail
             */
            $mail = GeneralUtility::makeInstance(MailMessage::class);
            //Betreff
            $mail->setSubject('Freifunk Pinneberg: Knoten Benachrichtigung');
            //Absender
            $mail->setFrom(array('service@pinneberg.freifunk.net' => 'Freifunk Pinneberg'));
            //Empfänger
            $mail->setTo(array($abo->getEmail()));
            //Nachricht
            $body = "Hallo,\n";
            $body .= "Dein Knoten mit der ID " . $internalNode->getNodeId() . " und dem Namen " . $internalNode->getNodeName() . " ist Offline. \n";
            $body .= "So lange dein Knoten offline bleibt, wirst du keine Benachrichtigungen mehr erhalten.\n\n";
            $body .= "Wenn du für diesen Knoten in Zukunft keine Benachrichtigungen mehr erhalten möchtest, kannst du sie unter $url abbestellen.";
            $mail->setBody($body);
            //Senden
            if ($mail->send() < 1) {
                $this->scheduler->log('Mail could not be send: ' . $abo->getEmail(), 1);
                $hasError = true;
            } else {
                //Update last notification
                $abo->setLastNotification(new \DateTime());
                $this->aboRepository->update($abo);
            }
        }
        if ($hasError) {
            return false;
        }
        return true;
    }

    /**
     * Update Node Status
     *
     * @param Node $internalNode
     * @param array $externalNode
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @return void
     */
    private function updateNode(Node $internalNode, array $externalNode)
    {
        $internalNode->setOnline($externalNode['status']['online']);
        $internalNode->setNodeName($externalNode['name']);
        $internalNode->setLastChange(new \DateTime());
        $this->internalNodeRepository->update($internalNode);
    }
}