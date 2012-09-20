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
        $this->edit_date = new \Datetime();
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
     * @var datetime $edit_date
     *
     * @ORM\Column(name="edit_date", type="datetime")
     */
    private $edit_date;

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
     * @ORM\OneToMany(targetEntity="Inouire\MininetBundle\Entity\Image", mappedBy="post")
     */
    private $images;

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
     * Get all the images attached to this post
     */
    public function getImages(){
        return $this->images;
    }

    /**
     * Add an image to this post
     */
    public function addImage(\Inouire\MininetBundle\Entity\Image $image){
        $this->images[] = $image;
        $image->setPost($this);
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date){
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate(){
        return $this->date;
    }
    
    /**
     * Set edit date
     *
     * @param datetime $date
     */
    public function setEditDate($edit_date){
        $this->edit_date = $edit_date;
    }

    /**
     * Get edit date
     *
     * @return datetime 
     */
    public function getEditDate(){
        return $this->edit_date;
    }

    /*
     * Set post date and time to now
     */
    public function touchDate(){
        $this->date = new \Datetime();
        $this->edit_date = new \Datetime();
    }
    
    /*
     * Set post edit date and time to now
     */
    public function touchEditDate(){
        $this->edit_date = new \Datetime();
    }
    
    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content){
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent(){
        return $this->content;
    }

    /**
     * Check if the post has one or more images associated
     */
    public function getHasImages(){
         return count($this->images) > 0;
    }

    /**
     * Get content of the post, with html enhancements
     */
    public function getHtmlContent(){
        
        //retrieve plain content
        $content = $this->content;
        
        //remove opening braces (security)
        $content = str_replace('<','',$content);
        
        //add arrows
        $content = str_replace(array('->','=>'),'<i class="icon-arrow-right"></i>',$content);
        
        //add warning sign
        $content = str_replace('/!\\','<i class="icon-warning-sign"></i>',$content);
        
        //add horizontal separator
        $content = preg_replace('/([-]{3}[-]+)/i','--------------------------------------------------',$content);
        
        //add star
        $content = str_replace('*','<i class="icon-asterisk"></i>',$content);
        
        //replace hyperlinks (but doesn't check link validity at all)
        $hyperlink_pattern = '/((http|https):\/\/[^\s]+)/i';
        $hyperlink = '<a href="$1">$1</a>';
        $content = preg_replace($hyperlink_pattern, $hyperlink, $content);

        //replace carriage return
        $content = str_replace("\n","<br />",$content);
        
        return $content;
    }
    
    /**
     * Get a resume of the content of the post
     */   
    public function getResume() {
        
        //retrieve plain content
        $content = $this->content;
        $short_content = $content;
        
        $limit = 200;
        $break=".";
        $pad=" [...]";
        
        // return with no change if content is shorter than $limit
        if(strlen($content) > $limit){
            // is $break present between $limit and the end of the string?
            if(false !== ($breakpoint = strpos($content, $break, $limit))) { 
                if($breakpoint < strlen($content) - 1) { 
                    $short_content = substr($content, 0, $breakpoint) . $pad; 
                } 
            } 
        }
        
        return $short_content;
    }
    
    /**
     * Get the age of the post, in number of days
     */
    public function getAgeInDays(){
        
        $today = new \Datetime();
        $today->setTime(23,59);
        $post_age = $this->date->diff($today);
        return $post_age->format('%a');
    }

    
    public function getWeight(){
        //retrieve plain content
        $content = $this->content;
        
        //get length of content + "height" of comments
        $weight = strlen($content) + count($this->comments)*150;
        
        return $weight;
    }
    
    /**
     * Set published
     *
     * @param boolean $published
     */
    public function setPublished($published){
        $this->published = $published;
    }

    /**
     * Get published
     *
     * @return boolean 
     */
    public function getPublished(){
        return $this->published;
    }
}
