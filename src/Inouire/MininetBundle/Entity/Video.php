<?php

namespace Inouire\MininetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Inouire\MininetBundle\Entity\Tag;

/**
 * Inouire\MininetBundle\Entity\Image
 *
 * @ORM\Table(name="mininet_video")
 * @ORM\Entity(repositoryClass="Inouire\MininetBundle\Entity\VideoRepository")
 */
class Video{
    
    public function __construct(){
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
     * @var string name
     * 
     * @ORM\Column(name="name",type="string", length=255)
     */
    private $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="Inouire\MininetBundle\Entity\Post",inversedBy="videos")
     */
    private $post;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $filename
     */
    public function setName($name){
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName(){
        return $this->name;
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
    
    public function getAbsolutePath(){
        return $this->getUploadRootDir().'/'.$this->name;
    }
    
    public function getThumbnailAbsolutePath(){
        return $this->getUploadRootDir().'/thumbnail/'.$this->name.'.jpg';
    }

    public function getUploadRootDir(){
        return __DIR__.'/../../../../web/vid';
    }

}
