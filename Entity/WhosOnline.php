<?php

namespace Netpeople\WhosOnlineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Netpeople\WhosOnlineBundle\Entity\WhosOnline
 *
 * @ORM\Table(name="whos_online")
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
     * @ORM\Column(name="username", type="string", length=200)
     */
    private $username;

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
     *
     * @var boolean 
     */
    private $isActive;

    /**
     * @var datetime $lastActivity
     *
     * @ORM\Column(name="lastActivity", type="datetime")
     */
    private $lastActivity;

    function __construct($username = NULL, $ip = NULL)
    {
        $this->setUsername($username);
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
     * @param string $user
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
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

    public function setActive($active = TRUE)
    {
        $this->isActive = $active;
    }

    public function isActive()
    {
        return $this->isActive;
    }

}