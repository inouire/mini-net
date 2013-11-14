<?php

namespace Inouire\MininetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inouire\MininetBundle\Entity\Tag
 *
 * @ORM\Table(name="mininet_tag")
 * @ORM\Entity(repositoryClass="Inouire\MininetBundle\Entity\TagRepository")
 */
class Tag{


    public function __construct(){
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @ORM\ManyToMany(targetEntity="Inouire\MininetBundle\Entity\Image", inversedBy="tags")
     * @ORM\JoinTable(name="mininet_image_tag_link")
     **/
    private $images;

 
    /**
     * @var text $content
     *
     * @ORM\Column(name="name", type="text")
     */
    private $name;
    
    /**
     * Get the name of the tag
     */
    public function getName(){
        return $this->name;
    }

    /**
     * Get all the pictures with this tag
     */
    public function getImages(){
        return $this->images;
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }
    
}