<?php

namespace FFPI\FfpiNodeUpdates\Task;

use FFPI\FfpiNodeUpdates\Domain\Model\Node;
use FFPI\FfpiNodeUpdates\Domain\Repository\NodeRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Scheduler;

abstract class AbstractNodeTask extends AbstractTask
{
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
     * @var string
     */
    public $path;

    /**
     * pid for the storage
     *
     * @var int
     */
    public $pid;

    protected function initializeTask(): void
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $this->internalNodeRepository = $this->objectManager->get(NodeRepository::class);

        // Set the correct PID for the storage
        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $querySettings->setStoragePageIds([$this->pid]);
        $this->internalNodeRepository->setDefaultQuerySettings($querySettings);
    }

    /**
     * Create or update a Node based on external data
     *
     * @param array $externalNode
     * @return Node|null
     */
    protected function createOrUpdateNode(array $externalNode, $updateOnline = true): ?Node
    {
        $nodeId = $externalNode['id'] ?? $externalNode['node_id'] ?? $externalNode['nodeinfo']['node_id'] ?? null;

        if ($nodeId === null) {
            return null;
        }

        $internalNode = $this->internalNodeRepository->findOneByNodeId($nodeId);
        if ($internalNode === null) {
            // Node does not exist, create a new one
            $internalNode = new Node();
            $internalNode->setNodeId($nodeId);
        }

        // Update the node's properties
        $nodeName = $externalNode['name'] ?? $externalNode['nodeinfo']['hostname'] ?? null;
        if ($nodeName !== null) {
            $internalNode->setNodeName($nodeName);
        }

        if (isset($externalNode['role'])) {
            $internalNode->setRole($externalNode['role']);
        } elseif (isset($externalNode['flags']['gateway']) && $externalNode['flags']['gateway'] === true) {
            $internalNode->setRole('gate');
        }

        $onlineStatus = $externalNode['status']['online'] ?? $externalNode['flags']['online'] ?? null;
        if ($onlineStatus !== null && ($updateOnline || $internalNode->_isNew())) {
            $internalNode->setOnline($onlineStatus);
        }

        if($internalNode->_isDirty()) {
            $internalNode->setLastChange(new \DateTime());
        }

        // Save the node
        if ($internalNode->_isNew()) {
            $internalNode->setPid($this->pid);
            $this->internalNodeRepository->add($internalNode);
        } elseif ($internalNode->_isDirty()) {
            $this->internalNodeRepository->update($internalNode);
        }

        return $internalNode;
    }

    /**
     * Gets External Nodes from JSON
     *
     * @return array
     */
    protected function getExternalNodes(): array
    {
        $json = $this->getJson();
        $nodes = json_decode($json, true);
        if ($nodes == NULL) {
            $this->scheduler->log(json_last_error_msg(), 1);
        }
        $externalNodes = $nodes['nodes'];

        if (empty($externalNodes) || !is_array($externalNodes)) {
            // Wir können nichts tun, wenn keine externen Nodes vorhanden sind
            $this->scheduler->log('External Nodes are empty', 1);
            return [];
        } else {
            $externalNodesNew = [];
            foreach ($externalNodes as $externalNode) {
                // Prüfen, ob die Node ID in verschiedenen Pfaden vorhanden ist
                $nodeId = $externalNode['id']
                    ?? $externalNode['node_id']
                    ?? $externalNode['nodeinfo']['node_id']
                    ?? null;

                if ($nodeId !== null) {
                    // Die Node ID muss der Array-Schlüssel sein
                    $externalNodesNew[$nodeId] = $externalNode;
                } else {
                    // Falls keine gültige Node ID gefunden wird, logge eine Warnung
                    $this->scheduler->log('Node without valid ID found', 1);
                }
            }
            $externalNodes = $externalNodesNew;
        }
        return $externalNodes;
    }

    /**
     * Gets the JSON from a URL
     *
     * @return string
     */
    protected function getJson(): string
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'TYPO3 at ' . $_SERVER['HTTP_HOST']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $this->scheduler->log('Curl Error: ' . curl_error($curl), 1);
        }
        curl_close($curl);

        return $response;
    }

    /**
     * @param $node Node|array
     * @return bool
     */
    protected function isNodeOnline($node): bool
    {
        if($node instanceof Node){
            return $node->isOnline();
        }
        if(is_array($node) && ($node['status']['online'] || $node['flags']['online'])){
            return true;
        }
        return false;
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
}
