<?php

namespace Inouire\MininetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inouire\MininetBundle\Entity\Image
 *
 * @ORM\Table(name="mininet_image")
 * @ORM\Entity(repositoryClass="Inouire\MininetBundle\Entity\ImageRepository")
 */
class Image
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
     * @var string $filename
     *
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\ManyToOne(targetEntity="Inouire\MininetBundle\Entity\Post",inversedBy="images")
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
     * Set filename
     *
     * @param string $filename
     */
    public function setFilename($filename){
        $this->filename = $filename;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename(){
        return $this->filename;
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
}
