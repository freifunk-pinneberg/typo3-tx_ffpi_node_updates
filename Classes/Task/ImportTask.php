<?php
namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
     */
    protected $internalNodeRepository;

    /**
     * pid for the storage
     *
     * @var int $pid
     */
    public $pid;


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
        $externalNodes = $this->getExternalNodes();

        if (empty($externalNodes)) {
            $this->scheduler->log('Keine externen Nodes gefunden.', 1);
            return false;
        }

        foreach ($externalNodes['nodes'] as $externalNode) {
            $nodeId = $externalNode['id'];
            if (!$this->internalNodeRepository->findOneByNodeId($nodeId)) {
                //Node exisitert noch nicht
                $this->scheduler->log('Node ' . $nodeId . ' Existiert noch nicht. Starte Import', 0);
                $node = $this->objectManager->get('FFPI\FfpiNodeUpdates\Domain\Model\Node'); # new \FFPI\FfpiNodeUpdates\Domain\Model\Node();
                $node->setNodeId($nodeId);
                $node->setLastChange(new \DateTime());
                $node->setOnline($externalNode['status']['online']);
                $node->setPid($this->pid);
                $this->internalNodeRepository->add($node);

            }
        }
        $persistenceManager->persistAll();

        if ($hasError === true) {
            return false;
        }

        return true;

    }

    /**
     * This method returns the destination pid as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        return 'Page ID: ' . $this->pid;
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


}