<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Post;

class AlbumController extends Controller
{
    
    /*
     * Redirects to the album of the current year
     */
    public function viewCurrentAction(){
        return $this->redirect($this->generateUrl('album',array(
            'year' => date('Y')
        )));
    }
    
    /*
     * Handles the album of a given year
     */
    public function viewAction($year){
        
        //check validity of year given
        if( $year > 8000 || $year < 1){
            return $this->render('InouireMininetBundle:Default:errorPage.html.twig',array(
                'error_level'=> 'info',
                'error_title'=> 'Numéro d\'année invalide',
                'error_message' => 'Impossible de générer l\'album de l\'année '.$year,
                'follow_link' => $this->generateUrl('albums'),
                'follow_link_text' => 'Aller à l\'album de l\'année en cours',
            ));
        }
        
        //get entity manager and Image repository
        $em = $this->getDoctrine()->getEntityManager();
        $image_repo = $em->getRepository('InouireMininetBundle:Image');
        
        //get all the images of the posts of the requested year
        $image_list = $image_repo->getImagesOfYear($year);
        
        //check that some pictures are avalaible for this year
        if(count($image_list)>0){
            //render the automatic album
            return $this->render('InouireMininetBundle:Default:album.html.twig',array(
                'year' => $year,
                'image_list' => $image_list,
            ));
        }else{
            //render the 'no album' page
            return $this->render('InouireMininetBundle:Default:noAlbum.html.twig',array(
                'year' => $year,
            ));
        }

    }
    
    
}
