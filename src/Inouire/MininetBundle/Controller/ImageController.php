<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Image;
use Imagine\Gd\Imagine;

class ImageController extends Controller
{
    
    /**
     * Get image file (full size or thumbnail)
     */
    public function getImageAction(Image $image, $is_thumbnail=false)
    {
        // get corresponding file path
        if($is_thumbnail){
            $image_file=$image->getThumbnailAbsolutePath();
        }else{
            $image_file=$image->getAbsolutePath();
        }
        
        //prepare response
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('Cache-Control', 'private, max-age=2592000');//1 month
        
        //set file content
        $response->headers->set('Content-Type','image/jpeg');
        $response->headers->set('Content-Length',filesize($image_file));
        $response->setContent(file_get_contents($image_file));
        
        return $response;
    }
    
    /*
     * Delete an existing image
     */ 
    public function deleteImageAction(Image $image)
    {
        //get current user
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //check that this image exists and that it belongs to this user
        if($image==null ){
            $response_status = 'error';
            $response_message = 'image '.$image_id.' does not exist';
        }else if( $image->getPost()->getAuthor() != $user){
            $response_status = 'error';
            $response_message = 'image '.$image_id.' does not belong to you';
        } else {
            //remove image + thumbnail from disk
            $fs = $this->get('filesystem');
            $fs->remove($image->getAbsolutePath());
            $fs->remove($image->getThumbnailAbsolutePath());
            
            //delete from database
            $em->remove($image);
            $em->flush();
             
            $response_status = 'ok';
            $response_message = 'image '.$image_id.' has been deleted';
        }
        
        //render json response
        return $this->render('InouireMininetBundle:Post:ajaxResponse.json.twig',array(
            'status'=> $response_status,
            'message' => $response_message
        ));
    }
    
    /**
     * Rotate an existing image
     */
    public function rotateImageAction(Image $image)
    {
        // Get operation type: clockwise or counter clockwise ?
        $direction = $this->getRequest()->query->get('direction');
        if($direction == 'clockwise'){
            $angle='90';
        }else if($direction == 'counter-clockwise'){
            $angle='-90';
        }else{
            //illegal operation
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_level'=> 'bang',
                'error_title'=> 'Opération inconnue',
                'error_message' => $direction.' n\'est pas une opération de rotation d\'image connue. Utiliser clockwise ou counter-clockwise',
                'follow_link' => $this->generateUrl('new_post'),
                'follow_link_text' => 'Ecrire un post',
            )); 
        }
        
        // Check that this image belongs to this user
        $user = $this->container->get('security.context')->getToken()->getUser();
        if( $image->getPost()->getAuthor() != $user ){
            //the user is not the author-> throw an error
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_level'=> 'bang',
                'error_title'=> 'Opération non autorisé',
                'error_message' => 'Vous ne pouvez pas modifier cette image car vous n\'en êtes pas l\'auteur',
                'follow_link' => $this->generateUrl('new_post'),
                'follow_link_text' => 'Ecrire un post',
            )); 
        }
        
        // Open image, rotate it and save it
        $imagine = new Imagine();
        $image_to_rotate = $imagine->open($image->getAbsolutePath());
        $save_options = array('quality' => 100);
        $image_to_rotate->rotate($angle)
                        ->save($image->getAbsolutePath(),$save_options);

        
        // Regenerate thumbnail
        $this->get('inouire.thumbnailer')->generateThumbnailFromImage($image);
        // need to find a way to bust caches on thumbnails during post edition
        
        // Redirect to currently editing post
        return $this->redirect($this->generateUrl('edit_post',array(
            'id'=> $image->getPost()->getId()
        )));
    }
    
}
