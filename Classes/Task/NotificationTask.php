<?php
namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use FFPI\FfpiNodeUpdates\Domain\Model\Abo;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NotificationTask extends \TYPO3\CMS\Extbase\Scheduler\Task
{

    /**
     * @var string
     */
    protected $path = 'http://meshviewer.pinneberg.freifunk.net/data/nodelist.json'; //@todo Aus Typoscript auslesen

    /**
     * @var \FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository
     */
    protected $internalNodeRepository;

    /**
     * @var \FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository
     */
    protected $aboRepository;

    /**
     * Execute the Task
     *
     * @return bool
     */
    public function execute()
    {
        /**
         * @var boolean $hasError
         */
        $hasError = false;
        /**
         * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
         */
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        /**
         * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
         */
        $persistenceManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager');
        $this->internalNodeRepository = $objectManager->get('FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository');
        $this->aboRepository = $objectManager->get('FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository');

        $externalNodes = $this->getExternalNodes();
        if (empty($externalNodes)) {
            $this->scheduler->log('External Nodes are empty', 1);
            return false;
        }
        $internalNodes = $this->internalNodeRepository->findAll();

        foreach ($internalNodes as $internalNode) {
            /**
             * @var \FFPI\FfpiNodeUpdates\Domain\Model\Node $internalNode
             */
            $internalOnline = $internalNode->getOnline();
            if ($internalOnline === true) {
                //check remote status
                $nodeId = $internalNode->getNodeId();
                $externalNode = $externalNodes[$nodeId];
                if ($externalNode['status']['online'] == false) {
                    //Knoten ist von online nach offline gewechselt
                    $this->updateNode($internalNode, false);
                    if (!$this->sendNotification($internalNode)) {
                        return false;
                    }
                }

            }
        }
        //Save all updated nodes
        $persistenceManager->persistAll();

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
        //301 und 302 folgen
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
        return $nodes;
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

            /**
             * @var \TYPO3\CMS\Core\Mail\MailMessage
             */
            $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
            //Betreff
            $mail->setSubject('Freifunk Pinneberg: Knoten Benachrichtigung');
            //Absender
            $mail->setFrom(array('service@pinneberg.freifunk.net' => 'Freifunk Pinneberg'));
            //Empfänger
            $mail->setTo(array($abo->getEmail()));
            //Nachricht
            $mail->setBody("Hallo,\n Dein Knoten mit der ID " . $internalNode->getNodeId() . " ist Offline.");
            //Senden
            if ($mail->send() < 1) {
                $this->scheduler->log($mail->se);
                $hasError = true;
            }
        }
        return $hasError;
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
        $internalNode->setLastChange(time());
        $this->internalNodeRepository->update($internalNode);
    }
}