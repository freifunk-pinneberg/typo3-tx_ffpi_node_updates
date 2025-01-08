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

use Throwable;

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

        $externalNodes = $this->getExternalNodes();
        if (empty($externalNodes)) {
            $this->scheduler->log('No external Nodes found!', 1);
            return false;
        }

        foreach ($externalNodes as $externalNode) {
            $this->createOrUpdateNode($externalNode, false);
        }

        $this->persistenceManager->persistAll();

        return true;
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
