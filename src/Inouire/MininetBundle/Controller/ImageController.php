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
    
    
    //TODO this should be done as a service
    public function handleImageUpload($file,$post,$em){
        
        //create new image object
        $image = new Image();
        $image->setFile($file);
        $image->setPost($post);
        
        //save file to disk
        $newFilename = rand(1000000, 999999999).rand(1000000, 999999999);
        $image->getFile()->move($image->getUploadDir(), $newFilename);
        $filePath = $image->getUploadDir().'/'.$newFilename;
        
        //check that it is an image
        $is_an_image = $this->checkTypeImage($filePath);
        if(!$is_an_image){
            //render an error page 
            return $this->render('InouireMininetBundle:Default:errorPage.html.twig',array(
                'error_level'=> 'bang',
                'error_title'=> 'Impossible d\'envoyer ce fichier',
                'error_message' => 'Le fichier envoyé n\'est pas une image',
                'follow_link' => $this->generateUrl('edit_post',array('post_id' => $image->post_id )),
                'follow_link_text' => 'Revenir à l\'édition du post',
            ));
        }
        
        //get image orientation
        $orientation = $this->getImageOrientation($filePath);
        
        //try to guess the extension
        $file=new File($filePath,true);
        $extension = $file->guessExtension();
        if (!$extension) {// extension cannot be guessed
            $extension = 'bin';
        }
        $file->move($image->getUploadDir(), $newFilename.'.'.$extension);
        $image->setPath($newFilename.'.'.$extension);
        $image->setFile(null);        
        
        //persisting changes to database
        $em->persist($image);
        $em->flush();

        //automatic image rotation and resize (for low disk footprint)
        //TODO handle errors
        $this->rotateImage($image,$orientation);
        $this->resizeImage($image);
    }
        
    /*
     * Util function: check that the file is an image
     */
    private function checkTypeImage($filePath){
        try{
            $imagine = new Imagine();
            $image = $imagine->open($filePath);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }
    
    /*
     * Util function: get image orientation
     */
    private function getImageOrientation($filePath){

        //$logger = $this->get('logger');
        //default: normal orientation
        $orientation = 1;
                
        try{
            //get exif data
            $exif = exif_read_data($filePath, 0, true);
            //get IFDO.Orientation
            foreach ($exif as $key => $section) {
                foreach ($section as $name => $val) {
                    //$logger->info($key.'.'.$name.': '.$val);
                    if($key == 'IFD0' && $name == 'Orientation'){
                        $orientation = $val;
                    }
                }
            }
        }catch(\Exception $e){
            $orientation = 1;
        }
        
        return $orientation;
    }
    
    /*
     * Util function: rotate an image depending on its orientation
     */
    private function rotateImage($image,$orientation){
        
        //compute angle
        if( $orientation == 1){
            return;
        }else{        
            if( $orientation == 3 ){
                $angle = 180;
            }else if( $orientation == 6 ){
                $angle = -90;
            }else if( $orientation == 8 ){
                $angle = 90;
            }else{
                $angle = 0;
            }
        }
        
        //open image
        $imagine = new Imagine();
        $image_to_rotate = $imagine->open($image->getAbsolutePath());
           
        //rotate it and save it
        $save_options = array('quality' => 100);
        $image_to_rotate->rotate($angle)
                        ->save($image->getAbsolutePath(),$save_options);
                            
    }
    
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
     
    /*
     * Util function: resize an image
     */
    private function resizeImage($image){
        
        //open image
        $imagine = new Imagine();
        $image_to_resize = $imagine->open($image->getAbsolutePath());
        
        //get actual size
        $actual_size = $image_to_resize->getSize();
        
        //if necessary, resize to a height of 600, and save to disk with the same name
        if( $actual_size->getHeight() > 600 ){
            $new_size = $actual_size->heighten(600);
            $save_options = array('quality' => 90);
            $image_to_resize->resize($new_size)
                            ->save($image->getAbsolutePath(),$save_options);
        }
        
    }
    
    public function getImageAction($image_id){
        
        //get entity manager
        $em = $this->getDoctrine()->getEntityManager();
        
        //get image
        $image = $em->getRepository('InouireMininetBundle:Image')->find($image_id);
        
        //check that this image exists
        if($image==null ){
            //TODO put a better error image file
            $image_file=__DIR__.'/../../../../web/css/icons/exit.png';
            $status_code=404;
        } else {
            $image_file=$image->getAbsolutePath();
            $status_code=200;
        }
        
        //prepare response
        $response = new Response();
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Connection', 'keep-alive');
        $response->setStatusCode($status_code);
        
        //set some cache informations
        $response->setPrivate();
        $response->setMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        
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
            //delete image
            $em->remove($image);
            $response_status = 'ok';
            $response_message = 'image '.$image_id.' has been deleted';
  
            //persit changes in the database
            $em->flush();
        }
        
        //render json response
        return $this->render('InouireMininetBundle:Post:ajaxResponse.json.twig',array(
            'status'=> $response_status,
            'message' => $response_message
        ));
    }
    
    
}
