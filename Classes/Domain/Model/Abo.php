<?php

namespace FFPI\FfpiNodeUpdates\Domain\Model;

use DateTime;
use FFPI\FfpiNodeUpdates\Domain\Model\Node;
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
 * Abo
 */
class Abo extends AbstractEntity
{
    /**
     * email
     *
     * @var string
     */
    protected $email = '';

    /**
     * confirmed
     *
     * @var bool
     */
    protected $confirmed = false;

    /**
     * lastNotification
     *
     * @var DateTime
     */
    protected $lastNotification;

    /**
     * secret
     *
     * @var string
     */
    protected $secret = '';

    /**
     * node
     *
     * @var \FFPI\FfpiNodeUpdates\Domain\Model\Node
     */
    protected $node;

    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * Returns the confirmed
     *
     * @return bool $confirmed
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Sets the confirmed
     *
     * @param bool $confirmed
     * @return void
     */
    public function setConfirmed(bool $confirmed)
    {
        $this->confirmed = $confirmed;
    }

    /**
     * Returns the boolean state of confirmed
     *
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * Returns the lastNotification
     *
     * @return DateTime $lastNotification
     */
    public function getLastNotification(): ?DateTime
    {
        return $this->lastNotification;
    }

    /**
     * Sets the lastNotification
     *
     * @param DateTime $lastNotification
     * @return void
     */
    public function setLastNotification(DateTime $lastNotification)
    {
        $this->lastNotification = $lastNotification;
    }

    /**
     * Returns the secret
     *
     * @return string $secret
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Sets the secret
     *
     * @param string $secret
     * @return void
     */
    public function setSecret(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * Returns the node
     *
     * @return Node $node
     */
    public function getNode(): ?Node
    {
        return $this->node;
    }

    /**
     * Sets the node
     *
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Node $node
     * @return void
     */
    public function setNode(Node $node)
    {
        $this->node = $node;
    }
}
