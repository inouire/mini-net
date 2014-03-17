<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Video;

class AlbumController extends Controller
{
    
    /*
     * Redirects to the album of the current year
     */
    public function viewCurrentMonthAction(){
        return $this->redirect($this->generateUrl('album',array(
            'year' => date('Y'),
            'month' => date('m')
        )));
    }
    
    /*
     * Handles the album of a given year
     */
    public function viewAction($year,$month){
        
        //check validity of year and month given
        if( $year > 8000 || $year < 1 || $month < 1 || $month > 12){
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_title'=> 'Date invalide',
                'error_message' => 'Impossible de générer l\'album du mois '.$month.' / année '.$year,
                'follow_link' => $this->generateUrl('albums'),
                'follow_link_text' => 'Aller à l\'album du mois en cours',
            ));
        }
        
        //get all the images of the posts of the requested year
        $em = $this->getDoctrine()->getManager();
        $image_repo = $em->getRepository('InouireMininetBundle:Image');
        $image_list = $image_repo->getImagesOfMonth($year,$month);
        
        //build date of current month
        $requested_date = new \Datetime($year.'-'.$month.'-01');
        
        //build months of year
        $months_of_year = array();
        for( $m = 1 ; $m <=12 ; $m++){
            $months_of_year[] = new \Datetime('2000-'.$m.'-01');
        }
        
        //check that some pictures are avalaible for this year
        if(count($image_list)>0){
            //render the automatic album
            return $this->render('InouireMininetBundle:Main:album.html.twig',array(
                'image_list' => $image_list,
                'requested_date' => $requested_date,
                'requested_year' => $year,
                'requested_month' => $month,
                'months_of_year' => $months_of_year
            ));
        }else{
            //render the 'no album' page
            return $this->render('InouireMininetBundle:Empty:noAlbum.html.twig',array(
                'requested_date' => $requested_date,
                'requested_year' => $year,
                'requested_month' => $month,
                'months_of_year' => $months_of_year
            ));
        }

    }

    /**
     * View the album of all videos
     */
    public function viewVideosAction(){
        
        //get all the videos
        $em = $this->getDoctrine()->getManager();
        $video_repo = $em->getRepository('InouireMininetBundle:Video');
        $video_list = $video_repo->getAllVideos();

        // display gallery
        return $this->render('InouireMininetBundle:Main:videos.html.twig',array(
            'video_list' => $video_list,
        ));
    }
    
}
