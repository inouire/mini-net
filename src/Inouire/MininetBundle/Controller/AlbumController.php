<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AlbumController extends Controller
{
    
    /**
     * Redirects to the pictures album of the current year
     */
    public function viewCurrentMonthPicturesAlbumAction(){
        return $this->redirect($this->generateUrl('album',array(
            'year' => date('Y'),
            'month' => date('m')
        )));
    }
    
    /**
     * Display the pictures album of a given year/month
     */
    public function viewPicturesAlbumAction($year,$month){
        
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
     * Display the pictures album of a given tag
     */
    public function viewTagAlbumAction($tag){
     
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
    
    /**
     * View the album of all videos
     */
    public function viewVideosAlbumAction(){
        //get all the videos
        $em = $this->getDoctrine()->getManager();
        $video_repo = $em->getRepository('InouireMininetBundle:Video');
        $video_list = $video_repo->getAllVideos();

        // display video gallery
        return $this->render('InouireMininetBundle:Main:videos.html.twig',array(
            'video_list' => $video_list,
        ));
    }
    
}
