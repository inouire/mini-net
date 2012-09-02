<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;


class ImageController extends Controller
{
    
    public function formImageAction($post_id){
        
        //create form for this post
        $image = new Image();
        $image->post_id = $post_id;
        
        $form = $this->createFormBuilder($image)
            ->add('file','file')
            ->add('post_id','hidden')
            ->getForm();
            
        return $this->render('InouireMininetBundle:Default:imageForm.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
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

                return $this->redirect($this->generateUrl('edit_post',array('post_id' => $image->post_id )));
                
            }   
        }else{
            return $this->render('InouireMininetBundle:Default:imageForm.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }
    
    public function getImageAction($image_id){

        
    }
    
    
}
