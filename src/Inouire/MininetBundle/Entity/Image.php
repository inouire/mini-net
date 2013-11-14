<?php

namespace Inouire\MininetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inouire\MininetBundle\Entity\Image
 *
 * @ORM\Table(name="mininet_image")
 * @ORM\Entity(repositoryClass="Inouire\MininetBundle\Entity\ImageRepository")
 */
class Image{
    
    public function __construct(){
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @var string path
     * 
     * @ORM\Column(name="path",type="string", length=255)
     */
    private $path;
    
    /**
     * @ORM\ManyToOne(targetEntity="Inouire\MininetBundle\Entity\Post",inversedBy="images")
     */
    private $post;

    /**
     * @ORM\ManyToMany(targetEntity="Inouire\MininetBundle\Entity\Tag", mappedBy="images")
     **/
    private $tags;
    
    //non mapped fields:
    private $file;
    private $post_id;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Set path
     *
     * @param string $filename
     */
    public function setPath($path){
        $this->path = $path;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath(){
        return $this->path;
    }

    /**
     * Get list of tags
     * 
     * @return 
     */
    public function getTags(){
        return $this->tags;
    }
    
    /**
     * Get the corresponding post
     * @return \Inouire\MininetBundle\Entity\Post
     */
    public function getPost(){
        return $this->post;
    }
    

    public function getFile(){
        return $this->file;
    }
    public function setFile($file){
        $this->file = $file;
    }
    
    public function getPostId(){
        return $this->post_id;
    }
    public function setPostId($id){
        $this->post_id = $id;
    }

    /**
     * Set the corresponding post
     */
    public function setPost(\Inouire\MininetBundle\Entity\Post $post){
        $this->post = $post;
    }
    
    //copied from cookbook
    public function getAbsolutePath(){
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath(){
        return null === $this->path ? null : '/'.$this->getUploadDir().'/'.$this->path;
    }

    public function getUploadRootDir(){
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir(){
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'img';
    }
}
