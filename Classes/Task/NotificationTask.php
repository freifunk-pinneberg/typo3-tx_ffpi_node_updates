<?php
namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Repository;

class NotificationTask extends \TYPO3\CMS\Extbase\Scheduler\Task
{

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
     * @return bool
     */
    public function execute()
    {
        $externalNodes = $this->getExternalNodes();
        if (empty($externalNodes)) {
            return false;
        }
        $internalNodes = $this->internalNodeRepository->findAll();

        foreach ($internalNodes as $internalNode){
            $internalOnline = $internalNode->getOnline();
            if($internalOnline === true){
                //check remote status
            }
        }

        return true;
    }

    /**
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

    private function getExternalNodes()
    {
        $json = $this->getJson();
        $nodes = json_decode($json);
        return $nodes;
    }
}