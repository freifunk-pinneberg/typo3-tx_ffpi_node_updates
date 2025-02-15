<?php

namespace FFPI\FfpiNodeUpdates\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

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

/**
 * Freifunk Nodes
 */
class Node extends AbstractEntity
{
    /**
     * nodeId
     *
     * @var string
     */
    protected $nodeId = '';

    /**
     * nodeName
     *
     * @var string
     */
    protected $nodeName = '';

    /**
     * online
     *
     * @var bool
     */
    protected $online = false;

    /**
     * lastChange
     *
     * @var DateTime
     */
    protected $lastChange = null;

    /**
     * role
     *
     * @var string
     */
    protected $role = '';

    /**
     * @var string
     */
    protected $domain = '';

    /**
     * @var string
     */
    protected $hardwareModel = '';

    /**
     * @var DateTime
     */
    protected $firstSeen = null;

    /**
     * @var DateTime
     */
    protected $lastSeen = null;

    /**
     * Returns the nodeId
     *
     * @return string $nodeId
     */
    public function getNodeId(): string
    {
        return $this->nodeId;
    }

    /**
     * Sets the nodeId
     *
     * @param string $nodeId
     * @return void
     */
    public function setNodeId(string $nodeId): void
    {
        $this->nodeId = $nodeId;
    }

    /**
     * @return string
     */
    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    /**
     * @param string $nodeName
     */
    public function setNodeName(string $nodeName): void
    {
        $this->nodeName = $nodeName;
    }

    /**
     * Returns the online
     *
     * @return bool $online
     */
    public function getOnline(): bool
    {
        return $this->online;
    }

    /**
     * Sets the online
     *
     * @param bool $online
     * @return void
     */
    public function setOnline(bool $online): void
    {
        $this->online = $online;
    }

    /**
     * Returns the boolean state of online
     *
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * Returns the lastChange
     *
     * @return DateTime $lastChange
     */
    public function getLastChange(): ?DateTime
    {
        return $this->lastChange;
    }

    /**
     * Sets the lastChange
     *
     * @param DateTime $lastChange
     * @return void
     */
    public function setLastChange(DateTime $lastChange): void
    {
        $this->lastChange = $lastChange;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * Get a clean label
     * @return string
     */
    public function getLabel(): string
    {
        if (!empty($this->getNodeName())) {
            $label = $this->getNodeName() . ' - ' . $this->getNodeId();
        } else {
            $label = $this->getNodeId();
        }
        return $label;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getHardwareModel(): string
    {
        return $this->hardwareModel;
    }

    /**
     * @param string $hardwareModel
     */
    public function setHardwareModel(string $hardwareModel): void
    {
        $this->hardwareModel = $hardwareModel;
    }

    /**
     * @return DateTime
     */
    public function getFirstSeen(): ?DateTime
    {
        return $this->firstSeen;
    }

    /**
     * @param DateTime|null $firstSeen
     */
    public function setFirstSeen(?DateTime $firstSeen): void
    {
        $this->firstSeen = $firstSeen;
    }

    /**
     * @return DateTime
     */
    public function getLastSeen(): ?DateTime
    {
        return $this->lastSeen;
    }

    /**
     * @param DateTime|null $lastSeen
     */
    public function setLastSeen(?DateTime $lastSeen): void
    {
        $this->lastSeen = $lastSeen;
    }
}
