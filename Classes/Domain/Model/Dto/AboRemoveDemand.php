<?php

namespace FFPI\FfpiNodeUpdates\Domain\Model\Dto;

class AboRemoveDemand
{
    /**
     * secret
     *
     * @var string
     */
    protected $secret = '';

    /**
     * email
     *
     * @var string
     */
    protected $email = '';

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
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
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }
}
