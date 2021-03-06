<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ArchivesController extends Controller
{

    /**
     * Redirect archives root URL to the current year-month archives
     */
    public function viewCurrentMonthAction(){
                
        return $this->redirect($this->generateUrl('posts',array(
            'year' => date('Y'),
            'month' => date('m'),
        )));
        
    }
    
    /**
     * Redirect to archives for attachment sharing
     */
    public function selectPostToShareFromArchivesAction(){
                
        return $this->redirect($this->generateUrl('posts',array(
            'year' => date('Y'),
            'month' => date('m'),
            'share' => 1
        )));
        
    }
    
    /**
     * Handles the archives of a given year and month
     */
    public function postsAction($year,$month,Request $request){

        // check validity of year and month given
        if( $year > 8000 || $year < 1 || $month < 1 || $month > 12){
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_title'=> 'Date invalide',
                'error_message' => 'Impossible de récupérer les posts du mois '.$month.' / année '.$year,
                'follow_link' => $this->generateUrl('archives'),
                'follow_link_text' => 'Aller aux archives du mois en cours',
            ));
        }
        
        // retrieve posts of the requested month
        $em = $this->getDoctrine()->getManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        $monthly_posts = $post_repo->getMonthlyPosts($year,$month);
       
        // build date of current month
        $requested_date = new \Datetime($year.'-'.$month.'-01');
        
        // build months of year
        $months_of_year = array();
        for( $m = 1 ; $m <=12 ; $m++){
            $months_of_year[] = new \Datetime('2000-'.$m.'-01');
        }
        
        // retrieve the "sharelink selection" option if any
        $sharelink_selection = ($request->query->get('share') != null);
        
        // check that there are some posts for this month
        if( count($monthly_posts) > 0 ){
            return $this->render('InouireMininetBundle:Main:archives.html.twig',array(
                'post_list'=> $monthly_posts,
                'requested_date' => $requested_date,
                'requested_year' => $year,
                'requested_month' => $month,
                'months_of_year' => $months_of_year,
                'share_selection' => $sharelink_selection,
            ));
        }else{ //if no post available, display the specific page
            return $this->render('InouireMininetBundle:Empty:noArchives.html.twig',array(
                'requested_date' => $requested_date,
                'requested_year' => $year,
                'requested_month' => $month,
                'months_of_year' => $months_of_year,
                'share_selection' => $sharelink_selection,
            ));
        }
        
    }
    
}
