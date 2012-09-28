<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Post;

class DefaultController extends Controller
{
    /*
     * Handles redirection of the root / of the website
     */
    public function rootAction(){
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

        return $this->render('InouireMininetBundle:Default:home.html.twig',array(
            'post_list'=> $post_list,
            'post_secondary_list' => $post_secondary_list
        ));
    }
    
    /*
     * Redirect archives root URL to the correct year-month archives
     */
    public function archivesAction(){
        
        $month = date('m');
        $year = date('Y');
        
        $prev_month = $month -1;
        if($prev_month <1){
            $prev_month = 12;
            $year = $year -1;
        }
        return $this->redirect($this->generateUrl('posts',array(
            'year' => $year,
            'month' => $prev_month,
        )));
    }
    
    /*
     * Handles the archives of a given year and month
     */
    public function postsAction($year,$month){

        //get entity manager and post repository
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        
        //retrieve posts of the requested month
        $monthly_posts = $post_repo->getMonthlyPosts($year,$month);
       
        //build date of current month
        $requested_date = new \Datetime($year.'-'.$month.'-01');
        
        //build months of year
        $months_of_year = array();
        for( $m = 1 ; $m <=12 ; $m++){
            $months_of_year[] = new \Datetime('2000-'.$m.'-01');
        }
        
        //check that there are some posts for this month
        if( count($monthly_posts) > 0 ){
            return $this->render('InouireMininetBundle:Default:posts.html.twig',array(
                'post_list'=> $monthly_posts,
                'requested_date' => $requested_date,
                'requested_year' => $year,
                'requested_month' => $month,
                'months_of_year' => $months_of_year
            ));
        }else{ //if no post available, display the specific page
            return $this->render('InouireMininetBundle:Default:noPosts.html.twig',array(
                'requested_date' => $requested_date,
                'requested_year' => $year,
                'requested_month' => $month,
                'months_of_year' => $months_of_year
            ));
        }
        
    }
    
    
}
