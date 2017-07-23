<?php
namespace FFPI\FfpiNodeUpdates\Domain\Model;

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
class Node extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
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
     * @var \DateTime
     */
    protected $lastChange = null;

    /**
     * Returns the nodeId
     *
     * @return string $nodeId
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * Sets the nodeId
     *
     * @param string $nodeId
     * @return void
     */
    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;
    }

    /**
     * @return string
     */
    public function getNodeName(){
        return $this->nodeName;
    }

    /**
     * @param string $nodeName
     */
    public function setNodeName($nodeName){
        $this->nodeName = $nodeName;
    }

    /**
     * Returns the online
     *
     * @return bool $online
     */
    public function getOnline()
    {
        return $this->online;
    }

    /**
     * Sets the online
     *
     * @param bool $online
     * @return void
     */
    public function setOnline($online)
    {
        $this->online = $online;
    }

    /**
     * Returns the boolean state of online
     *
     * @return bool
     */
    public function isOnline()
    {
        return $this->online;
    }

    /**
     * Returns the lastChange
     *
     * @return \DateTime $lastChange
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * Sets the lastChange
     *
     * @param \DateTime $lastChange
     * @return void
     */
    public function setLastChange(\DateTime $lastChange)
    {
        $this->lastChange = $lastChange;
    }
}
