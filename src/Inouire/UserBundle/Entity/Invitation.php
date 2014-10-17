<?php

namespace Inouire\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mininet_invitation")
 */
class Invitation
{
    /**
     * @ORM\Id @ORM\Column(type="string", length=8)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", length=250, unique=true)
     */
    protected $email;

    /** 
     * @ORM\OneToOne(targetEntity="User", mappedBy="invitation", cascade={"persist", "merge"})
     */
    protected $user;

    public function __construct()
    {
        // generate identifier only once, here a 8 characters length code
        $this->code = substr(md5(uniqid(rand(), true)), 0, 8);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
