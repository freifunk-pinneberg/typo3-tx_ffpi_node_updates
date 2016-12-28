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
use TYPO3\CMS\Core\Utility\DebugUtility;
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
    protected $path = 'http://meshviewer.pinneberg.freifunk.net/data/nodelist.json'; //@todo get from TypoScript

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

        //Get the external nodes
        $externalNodes = $this->getExternalNodes();

        //set the correct pid for the storage, get from the TYPO3 task settings ($this->pid)
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $querySettings->setRespectStoragePage(FALSE);
        $querySettings->setStoragePageIds(array($this->pid));
        $this->internalNodeRepository->setDefaultQuerySettings($querySettings);

        //check if we have external nodes
        if (empty($externalNodes)) {
            $this->scheduler->log('No external Nodes found!', 1);
            //if we don't have them, we can't do anything
            return false;
        }

        foreach ($externalNodes['nodes'] as $externalNode) {
            $nodeId = $externalNode['id'];
            if ($this->internalNodeRepository->findOneByNodeId($nodeId) === null) {
                //Node dose not exist
                $this->scheduler->log('Node ' . $nodeId . ' dose not exist. Start import', 0);
                //create an new object
                $node = $this->objectManager->get('FFPI\FfpiNodeUpdates\Domain\Model\Node');
                $node->setNodeId($nodeId);
                $node->setLastChange(new \DateTime());
                $node->setOnline($externalNode['status']['online']);
                $node->setPid($this->pid);
                //add the object to the repo
                $this->internalNodeRepository->add($node);
            }
        }

        $this->scheduler->log('Total nodes internal: ' . $this->internalNodeRepository->countAll(), 0);

        //Save all changes in the Database
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
        DebugUtility::debug($body, 'JSON Raw');
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
            $this->scheduler->log('json_decode: ' . json_last_error_msg(), 1);
        }
        return $nodes;
    }


}