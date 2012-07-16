<?php

namespace Inouire\MininetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inouire\MininetBundle\Entity\Comment
 *
 * @ORM\Table(name="mininet_comment")
 * @ORM\Entity(repositoryClass="Inouire\MininetBundle\Entity\CommentRepository")
 */
class Comment{
	
	public function __construct(){
        $this->date = new \Datetime();
    }
    
    /**
     * @var integer $id
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
	 * @ORM\ManyToOne(targetEntity="Inouire\MininetBundle\Entity\Post",inversedBy="comments")
	 */
	private $post;
	

	/**
	 * Get the author of the comment 
	 * @return \Inouire\UserBundle\Entity\User
	 */
    public function getAuthor(){
        return $this->author;
    }

	/**
	 * Set the author of the comment 
	 */
    public function setAuthor(\Inouire\UserBundle\Entity\User $author){
        $this->author = $author;
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
    }
    
    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var text $content
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;


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
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent()
    {
        return $this->content;
    }
}
