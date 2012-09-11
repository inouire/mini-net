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
       
		//build date of current month
		$current_date = new \Datetime($year.'-'.$month.'-01');
		
		//build months of year
		$months_of_year = array();
		for( $m = 1 ; $m <=12 ; $m++){
			$months_of_year[] = new \Datetime('2000-'.$m.'-01');
		}
		
        return $this->render('InouireMininetBundle:Default:posts.html.twig',array(
            'post_list'=> $monthly_posts,
            'current_date' => $current_date,
            'current_year' => $year,
            'current_month' => $month,
            'months_of_year' => $months_of_year
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
