<?php


namespace FFPI\FfpiNodeUpdates\Domain\Model;


use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Gateway extends AbstractEntity
{
    public const STATE_UNKNOWN = 0;
    public const STATE_OK = 1;
    public const STATE_ERROR = 2;

    /** @var \FFPI\FfpiNodeUpdates\Domain\Model\Node */
    protected $node = null;

    /** @var string $httpAdress */
    protected $httpAdress = '';

    /** @var \DateTime */
    protected $lastHealthCheck = null;

    /** @var \DateTime */
    protected $lastHealthChange = null;

    /** @var float|null $ping */
    protected $ping = null;

    /** @var int */
    protected $openVPN = self::STATE_UNKNOWN;

    /** @var int */
    protected $networkInterface = self::STATE_UNKNOWN;

    /** @var int */
    protected $firewall = self::STATE_UNKNOWN;

    /** @var int */
    protected $exitVpn = self::STATE_UNKNOWN;

    /**
     * @return Node
     */
    public function getNode(): Node
    {
        return $this->node;
    }

    /**
     * @param Node $node
     * @return Gateway
     */
    public function setNode(Node $node): Gateway
    {
        $this->node = $node;
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpAdress(): string
    {
        return $this->httpAdress;
    }

    /**
     * @param string $httpAdress
     * @return Gateway
     */
    public function setHttpAdress(string $httpAdress): Gateway
    {
        $this->httpAdress = $httpAdress;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastHealthCheck(): \DateTime
    {
        return $this->lastHealthCheck;
    }

    /**
     * @param \DateTime $lastHealthCheck
     * @return Gateway
     */
    public function setLastHealthCheck(\DateTime $lastHealthCheck): Gateway
    {
        $this->lastHealthCheck = $lastHealthCheck;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastHealthChange(): \DateTime
    {
        return $this->lastHealthChange;
    }

    /**
     * @param \DateTime $lastHealthChange
     * @return Gateway
     */
    public function setLastHealthChange(\DateTime $lastHealthChange): Gateway
    {
        $this->lastHealthChange = $lastHealthChange;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPing(): ?float
    {
        return $this->ping;
    }

    /**
     * @param float|null $ping
     * @return Gateway
     */
    public function setPing(?float $ping): Gateway
    {
        $this->ping = $ping;
        return $this;
    }

    /**
     * @return int
     */
    public function getOpenVPN(): int
    {
        return $this->openVPN;
    }

    /**
     * @param int
     * @return Gateway
     */
    public function setOpenVPN(int $openVPN): Gateway
    {
        $this->openVPN = $openVPN;
        return $this;
    }

    /**
     * @return int
     */
    public function getNetworkInterface(): int
    {
        return $this->networkInterface;
    }

    /**
     * @param int $networkInterface
     * @return Gateway
     */
    public function setNetworkInterface(int $networkInterface): Gateway
    {
        $this->networkInterface = $networkInterface;
        return $this;
    }

    /**
     * @return int
     */
    public function getFirewall(): int
    {
        return $this->firewall;
    }

    /**
     * @param int $firewall
     * @return Gateway
     */
    public function setFirewall(int $firewall): Gateway
    {
        $this->firewall = $firewall;
        return $this;
    }

    /**
     * @return int
     */
    public function getExitVpn(): int
    {
        return $this->exitVpn;
    }

    /**
     * @param int $exitVpn
     * @return Gateway
     */
    public function setExitVpn(int $exitVpn): Gateway
    {
        $this->exitVpn = $exitVpn;
        return $this;
    }


}