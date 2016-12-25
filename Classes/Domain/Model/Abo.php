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
 * Abo
 */
class Abo extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
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
     * @var \DateTime
     */
    protected $lastNotification = null;

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
    protected $node = null;

    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email)
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
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
    }

    /**
     * Returns the boolean state of confirmed
     *
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Returns the lastNotification
     *
     * @return \DateTime $lastNotification
     */
    public function getLastNotification()
    {
        return $this->lastNotification;
    }

    /**
     * Sets the lastNotification
     *
     * @param \DateTime $lastNotification
     * @return void
     */
    public function setLastNotification(\DateTime $lastNotification)
    {
        $this->lastNotification = $lastNotification;
    }

    /**
     * Returns the secret
     *
     * @return string $secret
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Sets the secret
     *
     * @param string $secret
     * @return void
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Returns the node
     *
     * @return \FFPI\FfpiNodeUpdates\Domain\Model\Node $node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Sets the node
     *
     * @param \FFPI\FfpiNodeUpdates\Domain\Model\Node $node
     * @return void
     */
    public function setNode(\FFPI\FfpiNodeUpdates\Domain\Model\Node $node)
    {
        $this->node = $node;
    }
}
