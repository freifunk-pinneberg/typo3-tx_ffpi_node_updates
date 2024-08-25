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
use TYPO3\CMS\Scheduler\Scheduler;

class ImportTask extends AbstractNodeTask
{

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

        $externalNodes = $this->getExternalNodes();
        if (empty($externalNodes)) {
            $this->scheduler->log('No external Nodes found!', 1);
            return false;
        }

        foreach ($externalNodes as $externalNode) {
            $this->createOrUpdateNode($externalNode, false);
        }

        $this->persistenceManager->persistAll();

        return !$hasError;
    }

    /**
     * This method returns the destination pid as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation(): string
    {
        $string = 'Storage Page: ' . $this->pid . "\n";
        $string .= 'nodes.json: ' . $this->path . "\n";

        return $string;
    }

}
