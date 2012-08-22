<?php

namespace Netpeople\WhosOnlineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Netpeople\WhosOnlineBundle\Entity\WhosOnline
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class WhosOnline
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var object $user
     *
     * @ORM\Column(name="user", type="object")
     */
    private $user;

    /**
     * @var string $ip
     *
     * @ORM\Column(name="ip", type="string", length=30)
     */
    private $ip;

    /**
     * @var datetime $lastLogin
     *
     * @ORM\Column(name="lastLogin", type="datetime")
     */
    private $lastLogin;

    /**
     * @var datetime $lastActivity
     *
     * @ORM\Column(name="lastActivity", type="datetime")
     */
    private $lastActivity;

    function __construct($user = NULL, $ip = NULL)
    {
        $this->setUser($user);
        $this->setIp($ip);
        $this->setLastLogin(new \DateTime());
        $this->setLastActivity(new \DateTime());
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param object $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return object 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set ip
     *
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set lastLogin
     *
     * @param datetime $lastLogin
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * Get lastLogin
     *
     * @return datetime 
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set lastActivity
     *
     * @param datetime $lastActivity
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;
    }

    /**
     * Get lastActivity
     *
     * @return datetime 
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

}