<?php

namespace Inouire\MininetBundle\Entity;


/**
 * Inouire\MininetBundle\Entity\PostForm
 *
 */
class PostForm{
        
            
    protected $content;

    protected $id;
    
    protected $file;

    public function getContent(){
        return $this->content;
    }
    public function setContent($content){
        $this->content = $content;
    }

    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;
    }

    public function getFile(){
        return $this->file;
    }
    public function setFile($file){
        $this->file = $file;
    }
    
}
