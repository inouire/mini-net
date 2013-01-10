<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Post;

class DefaultController extends Controller
{
    /*
     * Handles redirection of the old 'home' url to the root / of the website
     */
    public function oldHomeAction(){
        return $this->redirect($this->generateUrl('home'));
    }
    
    
    /**
     * Handles the home page 
     */
    public function homeAction(){
        
        //get entity manager and post repository
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        
        //retrieve last 6 published posts from database
        $post_list = $post_repo->findBy(
            array('published' => true),
            array('date' => 'desc'),
            8,
            0
        );
        
        $post_secondary_list = $post_repo->findBy(
            array('published' => true),
            array('date' => 'desc'),
            6,
            8
        );

        if(count($post_list)==0){
            return $this->render('InouireMininetBundle:Empty:emptyHome.html.twig');
        }else{
                return $this->render('InouireMininetBundle:Main:home.html.twig',array(
                    'post_list'=> $post_list,
                    'post_secondary_list' => $post_secondary_list
                ));
        }
    }
    
}
