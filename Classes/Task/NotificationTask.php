<?php
namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Repository;

class NotificationTask extends \TYPO3\CMS\Extbase\Scheduler\Task
{

    /**
     * @var string
     */
    protected $path = 'http://meshviewer.pinneberg.freifunk.net/data/nodelist.json'; //@todo Aus Typoscript auslesen

    /**
     * @var \FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository
     * @inject
     */
    protected $internalNodeRepository = '';

    /**
     * @var \FFPI\FfpiNodeUpdates\Domain\Repository\AboRepository
     * @inject
     */
    protected $aboRepository = '';

    /**
     * Execute the Task
     *
     * @return bool
     */
    public function execute()
    {
        $externalNodes = $this->getExternalNodes();
        if (empty($externalNodes)) {
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

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $body = substr($response, $header_size);

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
            if (!$mail->send()) {
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
     * @return boolean
     */
    private function updateNode($internalNode, $online)
    {
        $internalNode->setOnline($online);
        $internalNode->setLastChange(time());
        if ($this->internalNodeRepository->update($internalNode)) {
            return true;
        }
        return false;
    }
}