<?php


namespace FFPI\FfpiNodeUpdates\Domain\Model;

/***
 *
 * This file is part of the "Freifunk knoten Benachrichtigung" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Kevin Quiatkowski <kevin@pinneberg.freifunk.net>
 *
 ***/

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class FreifunkApiFile extends AbstractEntity
{
    /** @var string */
    protected $name = '';

    /** @var string */
    protected $jsonTemplate = '';

    /** @var int */
    protected $activeNodes = 0;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getJsonTemplate(): string
    {
        return $this->jsonTemplate;
    }

    /**
     * @param string $jsonTemplate
     */
    public function setJsonTemplate(string $jsonTemplate): void
    {
        $this->jsonTemplate = $jsonTemplate;
    }

    /**
     * @return int
     */
    public function getActiveNodes(): int
    {
        return $this->activeNodes;
    }

    /**
     * @param int $activeNodes
     */
    public function setActiveNodes(int $activeNodes): void
    {
        $this->activeNodes = $activeNodes;
    }

    /**
     * @return array<mixed>
     */
    public function getJson(): array
    {
        $jsonArray = json_decode($this->getJsonTemplate(), true);
        $jsonArray["state"]["nodes"] = $this->getActiveNodes();
        return $jsonArray;
    }
}
