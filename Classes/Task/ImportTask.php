<?php
namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Repository;

class ImportTask extends \TYPO3\CMS\Extbase\Scheduler\Task
{
    /**
     * Reference to a scheduler object
     *
     * @var \TYPO3\CMS\Scheduler\Scheduler
     */
    protected $scheduler;

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
     * Execute the Task
     *
     * @return bool
     */
    public function execute()
    {
        $hasError = false;
        $externalNodes = $this->getExternalNodes();
        $this->scheduler->log('External Nodes: '.$externalNodes, 0);
        if (empty($externalNodes)) {
            return false;
        }

        foreach ($externalNodes as $externalNode) {
            $nodeId = $externalNode['id'];
            if (!$this->internalNodeRepository->findOneByNodeId($nodeId)) {
                //Node exisitert noch nicht
                $node = new \FFPI\FfpiNodeUpdates\Domain\Model\Node();
                $node->setNodeId($nodeId);
                $node->setLastChange(time());
                $node->setOnline($externalNode['status']['online']);
                $node->setPid(111);

                if (!$this->internalNodeRepository->add($node)) {
                    $hasError = true;
                    $this->scheduler->log('Fehler beim Import', 1);
                } else {
                    $this->scheduler->log('Knoten ' . $nodeId . ' Importiert', 0);
                }

            }
        }

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


}