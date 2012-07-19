<?php

namespace Inouire\MininetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inouire\MininetBundle\Entity\Post
 *
 * @ORM\Table(name="mininet_post")
 * @ORM\Entity(repositoryClass="Inouire\MininetBundle\Entity\PostRepository")
 */
class Post{


    public function __construct(){
        $this->date = new \Datetime();
        $this->published = false;
        $this->content = "";
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
     * @ORM\OneToMany(targetEntity="Inouire\MininetBundle\Entity\Comment", mappedBy="post")
     */
    private $comments;

	/**
     * @var boolean $published
     *
     * @ORM\Column(name="published", type="boolean")
     */
	private $published;
	
	/**
	 * Get the author of the post 
	 * @return \Inouire\UserBundle\Entity\User
	 */
    public function getAuthor(){
        return $this->author;
    }

	/**
	 * Set the author of the post 
	 */
    public function setAuthor(\Inouire\UserBundle\Entity\User $author){
        $this->author = $author;
    }
    
    /**
     * Get all the comments on this post
     */
    public function getComments(){
        return $this->comments;
    }

	/**
	 * Add a comment for this post
	 */
    public function addComment(\Inouire\MininetBundle\Entity\Comment $comment){
        $this->comments[] = $comment;
        $comment->setPost($this);
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

    /**
     * Set published
     *
     * @param boolean $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * Get published
     *
     * @return boolean 
     */
    public function getPublished()
    {
        return $this->published;
    }
}
