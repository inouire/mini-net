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

class ImageController extends Controller
{
    
    /*
     * Handles image upload
     */
    public function addImageAction(){
        
        $image = new Image();
        
        $form = $this->createFormBuilder($image)
            ->add('file','file')
            ->add('post_id','hidden')
            ->getForm();
            
        if ($this->getRequest()->getMethod() === 'POST') {
            
            $form->bindRequest($this->getRequest());
            
            if ($form->isValid()) {
                
                //save file to disk
                $newFilename = rand(1000000, 999999999).rand(1000000, 999999999);
                $image->file->move($image->getUploadDir(), $newFilename);

                $logger = $this->get('logger');
                
                $orientation = 1;
                
                try{
                    //get exif data
                    $exif = exif_read_data($image->getUploadDir().'/'.$newFilename, 0, true);
                    //get IFDO.Orientation
                    foreach ($exif as $key => $section) {
                        foreach ($section as $name => $val) {
                            if($key == 'IFD0' && $name == 'Orientation'){
                                $orientation = $val;
                            }
                            $logger->info($key.$name.': '.$val);
                        }
                    }
                    $logger->info('Orientation of image is '.$orientation);                    
                }catch(\Exception $e){

                    $logger->info('The uploaded picture has no exif data');
                }
                
                //catch exceptions ?

                //try to guess the extension
                $file=new File($image->getUploadDir().'/'.$newFilename,true);
                
                $extension = $file->guessExtension();
                if (!$extension) {
                    // extension cannot be guessed
                    $extension = 'bin';
                }
                $file->move($image->getUploadDir(), $newFilename.'.'.$extension);
                
                //cleaning file property (not needed any more at this step)
                $image->file = null;
                
                //starting doctrine operations
                $em = $this->getDoctrine()->getEntityManager();
                $image->setPath($newFilename.'.'.$extension);
                $image->setPost($em->getRepository('InouireMininetBundle:Post')->find($image->post_id));
                $em->persist($image);
                $em->flush();

                //automatic image rotation and resize (for low disk footprint)
                //TODO handle errors
                $this->rotateImage($image,$orientation);
                $this->resizeImage($image);
                
                return $this->redirect($this->generateUrl('edit_post',array('post_id' => $image->post_id )));
                
            }   
        }else{
            return $this->render('InouireMininetBundle:Default:imageForm.html.twig', array(
                'form' => $form->createView(),
            ));
        }
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
        
        $response_status = 'ok';
        $response_message = 'it works';
        
        //get entity manager
        $em = $this->getDoctrine()->getEntityManager();
        
        //get image
        $image = $em->getRepository('InouireMininetBundle:Image')->find($image_id);
        
        //check that this image exists
        if($image==null ){
            $response_status = 'error';
            $response_message = 'image '.$image_id.' does not exist';
        } else {
            //TODO stream image
        }
        
        //render json response
        return $this->render('InouireMininetBundle:Post:ajaxResponse.json.twig',array(
            'status'=> $response_status,
            'message' => $response_message
        ));
        
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
