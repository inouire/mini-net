<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Image;
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
            8,
            0
        );
        
        $post_secondary_list = $post_repo->findBy(
            array('published' => true),
            array('date' => 'desc'),
            6,
            8
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
        
        $current_month = new \Datetime($year.'-'.$month.'-01');
        
        $month_prev = $month - 1;
        $year_prev = $year;
        if($month_prev<1){
            $month_prev = 12;
            $year_prev = $year - 1; 
        }
        $prev_month = new \Datetime($year_prev.'-'.$month_prev.'-01');
        
        $month_next = $month + 1;
        $year_next = $year;
        if($month_next>12){
            $month_next = 1;
            $year_next = $year + 1; 
        }
        $next_month = new \Datetime($year_next.'-'.$month_next.'-01');

        return $this->render('InouireMininetBundle:Default:posts.html.twig',array(
            'current_month' => $current_month,
            'prev_month' => $prev_month,
            'next_month' => $next_month,
            'post_list'=> $monthly_posts
        ));
    }
    
    
    public function albumAction(){
        
        //get entity manager and Post repository
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        
        //get all the posts
        $post_list = $post_repo->findBy(
            array('published' => true),
            array('date'=>'asc')
        );
          
        //render the automatic album
        return $this->render('InouireMininetBundle:Default:album.html.twig',array(
            'post_list' => $post_list,
        ));
    }
    
    
}
