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
use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Scheduler;

class ImportTask extends AbstractTask
{
    /**
     * Reference to a scheduler object
     *
     * @var Scheduler
     */
    protected $scheduler;

    /**
     * @var string
     */
    public $path;

    /**
     * @var NodeRepository
     */
    protected $internalNodeRepository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * pid for the storage
     *
     * @var int $pid
     */
    public $pid;

    protected function initializeTask(): void
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $this->internalNodeRepository = $this->objectManager->get(NodeRepository::class);

        //set the correct pid for the storage, get from the TYPO3 task settings ($this->pid)
        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(FALSE);
        $querySettings->setStoragePageIds([$this->pid]);
        $this->internalNodeRepository->setDefaultQuerySettings($querySettings);
    }

    /**
     * Execute the Task
     *
     * @return bool
     * @throws Throwable
     */
    public function execute(): bool
    {
        $this->initializeTask();
        $hasError = false;

        //Get the external nodes
        $externalNodes = $this->getExternalNodes();

        //check if we have external nodes
        if (empty($externalNodes)) {
            $this->scheduler->log('No external Nodes found!', 1);
            //if we don't have them, we can't do anything
            return false;
        }

        foreach ($externalNodes['nodes'] as $externalNode) {
            $nodeId = $externalNode['id'];
            $internalNode = $this->internalNodeRepository->findOneByNodeId($nodeId);
            if ($internalNode === null) {
                //Node dose not exist
                $this->scheduler->log('Node ' . $nodeId . ' dose not exist. Start import', 0);
                //create a new object
                $node = new Node;
                $node->setNodeId($nodeId);
                $node->setNodeName($externalNode['name']);
                $node->setRole($externalNode['role']);
                $node->setLastChange(new \DateTime());
                $node->setOnline($externalNode['status']['online']);
                $node->setPid($this->pid);
                //add the object to the repo
                $this->internalNodeRepository->add($node);
            } elseif ($internalNode instanceof Node) {
                $internalNode->setRole($externalNode['role']);
                $internalNode->setNodeName($externalNode['name']);
                $this->internalNodeRepository->update($internalNode);
            }
        }

        $this->scheduler->log('Total nodes internal: ' . $this->internalNodeRepository->countAll(), 0);

        //Save all changes in the Database
        $this->persistenceManager->persistAll();

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
    public function getAdditionalInformation(): string
    {
        return 'Storage Page: ' . $this->pid;
    }

    /**
     * Gets the JSON
     *
     * @return string
     */
    private function getJson(): string
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
    private function getExternalNodes(): array
    {
        $json = $this->getJson();
        $nodes = json_decode($json, true);
        if ($nodes == NULL) {
            $this->scheduler->log('json_decode: ' . json_last_error_msg(), 1);
        }
        return $nodes;
    }

}
