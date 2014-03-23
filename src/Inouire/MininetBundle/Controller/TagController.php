<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Tag;
use Inouire\MininetBundle\Entity\Image;

class TagController extends Controller
{
    /**
     * View the list of available tags
     */
    public function listAction(){
        //get all tags
        $em = $this->getDoctrine()->getManager();
        $tag_repo = $em->getRepository('InouireMininetBundle:Tag');   
        $tags = $tag_repo->findAll();
        
        // render tag list only
        return $this->render('InouireMininetBundle:Main:tags.html.twig',array(
            'tags' => $tags,
        ));

    }
    
    /**
     * Tag an image 
     */
    public function addTagToImageAction(Image $image, Tag $tag){
        //check that this image does not already have this tag
        $em = $this->getDoctrine()->getManager(); 
        if(!$image->getTags()->contains($tag)){
            //add tag to image
            $image->addTag($tag);
            $em->persist($image);
            $em->flush();
        }
        
        return $this->redirect($this->generateUrl('edit_post',array(
            'id' => $image->getPost()->getId()
        ))); 
        
    }
    
    /**
     * Untag an image
     */
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
            'id' => $image->getPost()->getId()
        ))); 
        
    }
    
    
}
