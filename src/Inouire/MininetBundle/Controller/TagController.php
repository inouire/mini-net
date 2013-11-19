<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Tag;
use Inouire\MininetBundle\Entity\Image;

class TagController extends Controller
{
    
    public function listAction(){
        
        //get entity manager and Tag repository
        $em = $this->getDoctrine()->getManager();
        $tag_repo = $em->getRepository('InouireMininetBundle:Tag');   
        
        //get all tags
        $tags = $tag_repo->findAll();
        
        return $this->render('InouireMininetBundle:Main:tags.html.twig',array(
            'tags' => $tags,
        ));

    }
    
    
    public function albumAction($tag){
     
        //get entity manager
        $em = $this->getDoctrine()->getManager();
        
        //check that the requested tag does exist
        //TODO
        
        //get all the images with the given tag
        $image_list = $em->getRepository('InouireMininetBundle:Image')
                         ->getImagesWithTag($tag);
        
        //get all tags
        $tags = $em->getRepository('InouireMininetBundle:Tag')
                   ->findAll();   
        
        
        return $this->render('InouireMininetBundle:Main:albumByTag.html.twig',array(
            'image_list' => $image_list,
            'tag' => $tag,
            'tags' => $tags
        ));
            
    }
    
    public function addTagToImageAction($image_id, $tag_id){
        
        //TODO use entity converter to avoid boilerplate code
        
        //get entities
        $em = $this->getDoctrine()->getManager(); 
        $image = $em->getRepository('InouireMininetBundle:Image')->find($image_id);
        $tag = $em->getRepository('InouireMininetBundle:Tag')->find($tag_id);
        
        //check that this image does not already have this tag
        if(!$image->getTags()->contains($tag)){
            //add tag to image
            $image->addTag($tag);
            $em->persist($image);
            $em->flush();
        }
        
        return $this->redirect($this->generateUrl('edit_post',array(
            'post_id' => $image->getPost()->getId()
        ))); 
        
    }
    
    public function removeTagFromImageAction($image_id, $tag_id){
        
        //TODO use entity converter to avoid boilerplate code
        
        //get entities
        $em = $this->getDoctrine()->getManager(); 
        $image = $em->getRepository('InouireMininetBundle:Image')->find($image_id);
        $tag = $em->getRepository('InouireMininetBundle:Tag')->find($tag_id);
        
        //check that this image has this tag
        //if($image->getTags()->contains($tag)){
            //add tag to image
            $image->removeTag($tag);
            $em->persist($image);
            //$em->persist($tag);
            $em->flush();
        //}
        
        return $this->redirect($this->generateUrl('edit_post',array(
            'post_id' => $image->getPost()->getId()
        ))); 
        
    }
    
    
}
