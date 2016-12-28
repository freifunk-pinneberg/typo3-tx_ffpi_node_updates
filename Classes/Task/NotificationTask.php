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

use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use FFPI\FfpiNodeUpdates\Domain\Model\Abo;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NotificationTask extends \TYPO3\CMS\Extbase\Scheduler\Task
{
    protected $constructDone = false;

    /**
     * @var string
     */
    protected $path = 'http://meshviewer.pinneberg.freifunk.net/data/nodelist.json'; //@todo get from TypoScript

    /**
     * @var \FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository
     */
    protected $internalNodeRepository;

    /**
     * @var \FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository
     */
    protected $aboRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
     */
    protected $uriBuilder;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;

    /**
     * NotificationTask constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->constructDone = true;

        /**
         * Builds URI for Frontend or Backend
         *
         * @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $this ->uriBuilder
         */
        $this->uriBuilder = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder');

        /**
         * the default Extbase Object Manager
         *
         * @var \TYPO3\CMS\Extbase\Object\ObjectManager $this ->objectManager
         */
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        /**
         * Saves the Repository objects into the Database
         *
         * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $this ->persistenceManager
         */
        $this->persistenceManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager');

        /**
         * Our Repository for the Freifunk Nodes
         *
         * @var \FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository
         */
        $this->internalNodeRepository = $this->objectManager->get('FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository');

        /**
         * Our Repository for the Abos
         *
         * @var \FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository
         */
        $this->aboRepository = $this->objectManager->get('FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository');

        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $querySettings->setRespectStoragePage(FALSE);
        $this->internalNodeRepository->setDefaultQuerySettings($querySettings);
    }

    /**
     * Execute the Task
     * The main function, will be executed by the scheduler each time
     *
     * @return bool
     */
    public function execute()
    {
        if (!$this->constructDone) {
            //I don't know why, but in test with TYPO3 7.6.14 an scheduler 7.6.0 the __construct is not automatic called
            $this->__construct();
        }

        /**
         * @var boolean $hasError
         */
        $hasError = false;

        //We need the External Nodes. (They come from the json file)
        $externalNodes = $this->getExternalNodes();

        if (empty($externalNodes)) {
            //We can't do anything when we don't have the exernal nodes
            $this->scheduler->log('External Nodes are empty', 1);
            return false;
        } else {
            $externalNodesNew = array();
            foreach ($externalNodes as $externalNode) {
                //the node id must be the array key
                $externalNodesNew[$externalNode['id']] = $externalNode;
            }
            $externalNodes = $externalNodesNew;
            unset($externalNodesNew);
        }
        /**
         * Array with all internal saved nodes
         * @var array $internalNodes
         */
        $internalNodes = $this->internalNodeRepository->findAll()->toArray();

        foreach ($internalNodes as $internalNode) {
            /**
             * A single node object
             * @var \FFPI\FfpiNodeUpdates\Domain\Model\Node $internalNode
             */

            /**
             * Online Status
             * @var boolean $internalOnline
             */
            $internalOnline = $internalNode->getOnline();
            //check remote status
            $nodeId = $internalNode->getNodeId();
            $externalNode = $externalNodes[$nodeId];
            #DebugUtility::debug($externalNode);

            if ($internalOnline === true AND $externalNode['status']['online'] === false) {
                //node changed from online to offline since last check
                $this->scheduler->log('Node ' . $nodeId . ' is now offline', 0);
                $this->updateNode($internalNode, false);
                if (!$this->sendNotification($internalNode)) {
                    //it was not possible to send a notification
                    $this->scheduler->log('Notification for ' . $nodeId . ' could not be send.', 1);
                    $hasError = true;
                }
            } elseif ($internalOnline != $externalNode['status']['online']) {
                //The status has been changed, update the object
                $this->updateNode($internalNode, $externalNode['status']['online']);
                //Log the change
                if ($externalNode['status']['online']) {
                    $this->scheduler->log('Node ' . $nodeId . ' is now online.', 0);
                } else {
                    $this->scheduler->log('Node ' . $nodeId . ' is now offline.', 0);
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
        return $nodes['nodes'];
    }

    /**
     * Send E-Mail notifications
     *
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Node $internalNode
     * @return boolean
     */
    private function sendNotification($internalNode)
    {
        $hasError = false;
        $abos = $this->aboRepository->findByNode($internalNode);
        foreach ($abos as $abo) {
            /**
             * @var \FFPI\FfpiNodeUpdates\Domain\Model\Abo $abo
             */

            //first, check if the abo is confirmed, we don't want to spam
            if (!$abo->getConfirmed()) {
                //Not confirmed, go to the next abo
                continue;
            }

            //build url to remove the abo
            $pid = $this->uriBuilder->getTargetPageUid();
            $urlAttributes = array();
            $urlAttributes['tx_ffpinodeupdates_nodeabo[action]'] = 'removeForm';
            $urlAttributes['tx_ffpinodeupdates_nodeabo[controller]'] = 'Abo';
            $urlAttributes['tx_ffpinodeupdates_nodeabo[email]'] = $abo->getEmail();
            $urlAttributes['tx_ffpinodeupdates_nodeabo[secret]'] = $abo->getSecret();
            $url = $this->uriBuilder;
            $url->reset();
            $url->setTargetPageUid($pid);
            $url->setCreateAbsoluteUri(true);
            $url->setArguments($urlAttributes);
            $url = $url->buildFrontendUri();

            //Create the e-mail
            //@todo make it multilingual
            //@todo use fluid templates
            /**
             * @var \TYPO3\CMS\Core\Mail\MailMessage $mail
             */
            $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
            //Betreff
            $mail->setSubject('Freifunk Pinneberg: Knoten Benachrichtigung');
            //Absender
            $mail->setFrom(array('service@pinneberg.freifunk.net' => 'Freifunk Pinneberg'));
            //Empfänger
            $mail->setTo(array($abo->getEmail()));
            //Nachricht
            $body = "Hallo,\n";
            $body .= "Dein Knoten mit der ID " . $internalNode->getNodeId() . " ist Offline. \n";
            $body .= "So lange dein Knoten offline bleibt, wirst du keine Benachrichtigungen mehr erhalten.\n\n";
            $body .= "Wenn du für diesen Knoten in Zukunft keine Benachrichtigungen mehr erhalten möchtest, kannst du sie unter $url abbestellen.";
            $mail->setBody($body);
            //Senden
            if ($mail->send() < 1) {
                $this->scheduler->log('Mail could not be send: ' . $abo->getEmail(), 1);
                $hasError = true;
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
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Node $internalNode
     * @param boolean $online
     * @return void
     */
    private function updateNode($internalNode, $online)
    {
        $internalNode->setOnline($online);
        $internalNode->setLastChange(new \DateTime());
        $this->internalNodeRepository->update($internalNode);
    }
}