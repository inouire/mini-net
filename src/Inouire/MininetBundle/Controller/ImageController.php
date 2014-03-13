<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ImageController extends Controller
{
    
    public function rotateImageAction($image_id){
        
        //get operation: clockwise or counter clockwise ?
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
        
        //get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //get image
        $em = $this->getDoctrine()->getEntityManager();
        $image = $em->getRepository('InouireMininetBundle:Image')->find($image_id);
        
        //check that this image exists and that it belongs to this user
        if($image==null ){
            //post doesn't exist
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_level'=> 'bang',
                'error_title'=> 'Image inexistante',
                'error_message' => 'L\'image sur laquelle vous souhaitez appliquer une rotation n\'existe pas.',
                'follow_link' => $this->generateUrl('new_post'),
                'follow_link_text' => 'Ecrire un post',
            )); 
        }else if( $image->getPost()->getAuthor() != $user ){
            //the user is not the author-> throw error
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_level'=> 'bang',
                'error_title'=> 'Opération non autorisé',
                'error_message' => 'Vous ne pouvez pas modifier cette image car vous n\'en êtes pas l\'auteur',
                'follow_link' => $this->generateUrl('new_post'),
                'follow_link_text' => 'Ecrire un post',
            )); 
        }
        
        //open image, rotate it and save it
        $imagine = new Imagine();
        $image_to_rotate = $imagine->open($image->getAbsolutePath());
        $save_options = array('quality' => 100);
        $image_to_rotate->rotate($angle)
                        ->save($image->getAbsolutePath(),$save_options);
                      
        //rename file (hack to force liip imagine bundle to re-generate cache (not very clean)
        //TODO use ClacheManager https://github.com/liip/LiipImagineBundle/issues/74
        $file=new File($image->getAbsolutePath(),true);
        $new_name = '9'.$image->getPath();
        $file->move($image->getUploadDir(), $new_name);
        $image->setPath($new_name);
        $em->persist($image);
        $em->flush();
        
        //redirect to currently editing post
        return $this->redirect($this->generateUrl('edit_post',array(
            'post_id'=> $image->getPost()->getId()
        )));
    }
     
    public function getImageAction($image_id, $is_thumbnail=false){
        
        //get image
        $em = $this->getDoctrine()->getEntityManager();
        $image = $em->getRepository('InouireMininetBundle:Image')->find($image_id);
        
        //check that this image exists
        if($image==null ){
            //TODO put a better error image file
            $image_file=__DIR__.'/../../../../web/css/icons/exit.png';
            $status_code=404;
        } else {
            if($is_thumbnail){
                $image_file=$image->getThumbnailAbsolutePath();
            }else{
                $image_file=$image->getAbsolutePath();
            }
            
            $status_code=200;
        }
        
        //prepare response
        $response = new Response();
        $response->setStatusCode($status_code);
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
     * Handles delete action on an image
     */ 
    public function deleteImageAction($image_id){
        
        //get entity manager
        $em = $this->getDoctrine()->getEntityManager();
        
        //get image
        $image = $em->getRepository('InouireMininetBundle:Image')->find($image_id);
        
        //get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //check that this image exists and that it belongs to this user
        if($image==null ){
            $response_status = 'error';
            $response_message = 'image '.$image_id.' does not exist';
        }else if( $image->getPost()->getAuthor() != $user){
            $response_status = 'error';
            $response_message = 'image '.$image_id.' does not belong to you';
        } else {
            //delete image from disk
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
    
    
}
