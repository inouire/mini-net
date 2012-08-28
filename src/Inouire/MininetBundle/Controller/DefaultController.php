<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Comment;

class DefaultController extends Controller
{
    
    public function rootAction(){
        return $this->redirect($this->generateUrl('home'));
    }
    
    public function homeAction(){
        
        //get entity manager and post repository
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        
        //retrieve last 6 published posts from database
        $post_list = $post_repo->findBy(
            array('published' => true),
            array('date' => 'desc'),
            6,
            0
        );
        
        $post_secondary_list = $post_repo->findBy(
            array('published' => true),
            array('date' => 'desc'),
            6,
            6
        );

        return $this->render('InouireMininetBundle:Default:home.html.twig',array(
            'post_list'=> $post_list,
            'post_secondary_list' => $post_secondary_list
        ));
    }
    
    public function postsAction($year,$month){

        //get entity manager and post repository
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        
        //retrieve posts of the requested month
        $monthly_posts = $post_repo->getMonthlyPosts($year,$month);
        
        return $this->render('InouireMininetBundle:Default:home.html.twig',array(
            'post_list'=> $monthly_posts
        ));
    }
    
    public function albumAction(){
        return $this->render('InouireMininetBundle:Default:album.html.twig');
    }
    
    
}
