<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Tag;
use Inouire\MininetBundle\Entity\Image;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
     * @ParamConverter("tag", class="InouireMininetBundle:Tag", options={"id" = "tag_id"})
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
     * @ParamConverter("tag", class="InouireMininetBundle:Tag", options={"id" = "tag_id"})
     */
    public function removeTagFromImageAction(Image $image, Tag $tag){
      
        //check that this image has this tag
        $em = $this->getDoctrine()->getManager(); 
        if($image->getTags()->contains($tag)){
            $image->removeTag($tag);
            $em->persist($image);
            $em->flush();
        }
        
        return $this->redirect($this->generateUrl('edit_post',array(
            'id' => $image->getPost()->getId()
        ))); 
        
    }
    
    /**
     * View the list of available tags
     */
    public function adminListAction(){
        
        $em = $this->getDoctrine()->getManager();
        
        // build add tag form
        $tag = new Tag();
        $form = $this->createFormBuilder($tag)
            ->add('name', 'text')
            ->add('add', 'submit', array('label' => 'Ajouter'))
            ->getForm();
        
        // use form data if the form has been submitted
        $form->handleRequest($this->getRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($tag);
            $em->flush();
        }
    
        //get all tags
        $tag_repo = $em->getRepository('InouireMininetBundle:Tag');   
        $tags = $tag_repo->findAll();
        
        // render tags list + form
        return $this->render('InouireMininetBundle:Admin:tags.html.twig',array(
            'tags' => $tags,
            'form' => $form->createView()
        ));
    }
    
    
}
