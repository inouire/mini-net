<?php

namespace Inouire\MininetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShareLink
 *
 * @ORM\Table(name="mininet_sharelink")
 * @ORM\Entity(repositoryClass="Inouire\MininetBundle\Entity\ShareLinkRepository")
 */
class ShareLink
{
    
    public function __construct(){
        
        //set expiration date to now + 8 days
        $this->expirationDate = new \Datetime();
        
        // build random token
        $pool = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $token_length = rand(32, 64);
        $token = '';
        for ($i = 0; $i < $token_length; $i++) {
            $token .= $pool[rand(0, strlen($pool) - 1)];
        }
        $this->token = $token;
    }
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Inouire\UserBundle\Entity\User")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Inouire\MininetBundle\Entity\Post",inversedBy="sharelinks")
     */
    private $post;

    /**
     * @var \DateTime
     * @ORM\Column(name="expiration_date", type="datetime")
     */
    private $expiration_date;

    /**
     * @var string
     * @ORM\Column(name="token", type="string", length=64)
     */
    private $token;


    /**
     * Get id
     * @return integer 
     */
    public function getId(){
        return $this->id;
    }
     
    /**
     * Set the author of the share link 
     */
    public function setAuthor(\Inouire\UserBundle\Entity\User $author){
        $this->author = $author;
        return $this;
    }

    /**
     * Get the author of the share link 
     * @return \Inouire\UserBundle\Entity\User
     */
    public function getAuthor(){
        return $this->author;
    }

    /**
     * Get the corresponding post
     * @return \Inouire\MininetBundle\Entity\Post
     */
    public function getPost(){
        return $this->post;
    }

    /**
     * Set the corresponding post
     */
    public function setPost(\Inouire\MininetBundle\Entity\Post $post){
        $this->post = $post;
        return $this;
    }

    /**
     * Set expirationDate
     * @param \DateTime $expiration_date
     */
    public function setExpirationDate($expiration_date)
    {
        $this->expiration_date = $expiration_date;
        return $this;
    }

    /**
     * Get expirationDate
     * @return \DateTime 
     */
    public function getExpirationDate()
    {
        return $this->expiration_date;
    }

    /**
     * Set token
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }
}
