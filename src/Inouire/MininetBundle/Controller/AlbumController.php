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
        
        //get entity manager and Image repository
        $em = $this->getDoctrine()->getEntityManager();
        $image_repo = $em->getRepository('InouireMininetBundle:Image');
        
        //get all the images of the posts of the requested year
        $image_list = $image_repo->getImagesOfYear($year);
        
        if(count($image_list)==0){
			//render the 'no album' page
			return $this->render('InouireMininetBundle:Default:noAlbum.html.twig',array(
				'year' => $year,
			));
		}else{
			//render the automatic album
			return $this->render('InouireMininetBundle:Default:album.html.twig',array(
				'year' => $year,
				'image_list' => $image_list,
			));
		}

        
        
    }
    
    
}
