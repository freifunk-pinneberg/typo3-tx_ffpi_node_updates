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

use TYPO3\CMS\Core\Site\SiteFinder;
use FFPI\FfpiNodeUpdates\Domain\Model\Node;
use FFPI\FfpiNodeUpdates\Domain\Model\Abo;
use FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository;
use FFPI\FfpiNodeUpdates\Utility\MailUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Service\ExtensionService;

class NotificationTask extends AbstractNodeTask
{
    /**
     * @var int
     */
    public $unsubscribePid;

    /**
     * @var AboRepository
     */
    protected $aboRepository;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    /**
     * @var ExtensionService
     */
    protected $extensionService;

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
        $this->initializeTask();
        $this->initialiseMainTask();
        return $this->mainTask();
    }

    protected function initialiseMainTask(): void
    {
        /**
         * DataMapFacotry, not directly used by this task, but needs to be aviable for the repository
         *
         * @var DataMapFactory $this- >dataMapFactory
         */
        $this->dataMapFactory = $this->objectManager->get(DataMapFactory::class);

        /**
         * Saves the Repository objects into the Database
         *
         * @var PersistenceManager $this- >persistenceManager
         */
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);

        /**
         * Our Repository for the Abos
         *
         * @var AboRepository $this- >aboRepository
         */
        $this->aboRepository = $this->objectManager->get(AboRepository::class);

        /**
         * @var SiteFinder $this- >siteFinder
         */
        $this->siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        /**
         * @var ExtensionService $this- >extensionService
         */
        $this->extensionService = $this->objectManager->get(ExtensionService::class);

        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $querySettings->setStoragePageIds([(int)$this->pid]);
        $querySettings->setRespectStoragePage(true);
        $querySettings->setRespectSysLanguage(false);
        //Set the settings for our repositorys
        $this->internalNodeRepository->setDefaultQuerySettings($querySettings);
        $this->aboRepository->setDefaultQuerySettings($querySettings);

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

    protected function updateAllNodes(array $externalNodes, QueryResultInterface $internalNodes): bool
    {
        $hasError = false;
        foreach ($internalNodes as $internalNode) {
            /** @var array|null $externalNode */
            $externalNode = $externalNodes[$internalNode->getNodeId()];

            if ($externalNode === null) {
                continue;
            }

            if ($internalNode->getOnline() === true && $this->isNodeOnline($externalNode) === false) {
                //node changed from online to offline since last check
                $this->scheduler->log('Node ' . $internalNode->getNodeId() . ' is now offline', 0);
                $this->createOrUpdateNode($externalNode, true);
                if (!$this->sendNotification($internalNode)) {
                    //it was not possible to send a notification
                    $this->scheduler->log('One or more Notifications for ' . $internalNode->getNodeId() . ' could not be send.', 1);
                    $hasError = true;
                }
            } elseif ($this->isNodeOnline($internalNode) !== $this->isNodeOnline($externalNode)) {
                //The status has been changed, update the object
                $this->createOrUpdateNode($externalNode, true);
                //Log the change
                if ($this->isNodeOnline($externalNode)) {
                    $this->scheduler->log('Node ' . $internalNode->getNodeId() . ' is now online.', 0);
                } else {
                    $this->scheduler->log('Node ' . $internalNode->getNodeId() . ' is now offline.', 0);
                }
            }
        }
        return !$hasError;
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
        $hasError = false;
        //build url to remove the abo
        $unsubscribeUrl = $this->buildUnsubscribeLink($abo);

        $emailData = [
            'node' => $internalNode,
            'url' => $unsubscribeUrl
        ];

        $mail = new MailUtility();
        $send = $mail->sendMail($abo->getEmail(), 'Freifunk Pinneberg: Knoten Benachrichtigung', 'Mail/Notification.html', $emailData, ['List-Unsubscribe' => $unsubscribeUrl]);

        if (!$send) {
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

    /**
     * @param Abo $abo
     * @return string
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    protected function buildUnsubscribeLink(Abo $abo): string
    {
        $pid = $this->unsubscribePid;

        $site = $this->siteFinder->getSiteByPageId($pid);
        $argumentsPrefix = $this->extensionService->getPluginNamespace('FfpiNodeUpdates', 'Nodeabo');

        $arguments = [
            $argumentsPrefix => [
                'action' => 'removeForm',
                'aboRemoveDemand' => [
                    'email' => $abo->getEmail(),
                    'secret' => $abo->getSecret()
                ]
            ]
        ];
        $url = (string)$site->getRouter()->generateUri((string)$pid, $arguments);

        return $url;
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
}
