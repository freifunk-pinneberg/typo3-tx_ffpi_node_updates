<?php

namespace FFPI\FfpiNodeUpdates\Domain\Model\Dto;

use TYPO3\CMS\Extbase\Annotation\Validate;
class AboNewDemand
{
    /**
     * nodeId
     *
     * @var string
     * @Validate("NotEmpty")
     */
    protected $nodeId = '';

    /**
     * email
     *
     * @var string
     * @Validate("NotEmpty")
     */
    protected $email = '';

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @param string $nodeId
     */
    public function setNodeId($nodeId): void
    {
        $this->nodeId = $nodeId;
    }
}
