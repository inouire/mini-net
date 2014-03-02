<?php

namespace Inouire\MininetBundle\Service;

use Doctrine\ORM\EntityManager;
use Inouire\MininetBundle\Service\ImageResize;
use Inouire\MininetBundle\Entity\Image;
use Symfony\Component\HttpFoundation\File\File;

class ImageUpload
{

    protected $em;
    protected $resizer;
    
    public function __construct(EntityManager $em, ImageResize $resizer)
    {
        $this->em = $em;
        $this->resizer = $resizer;
    }
    
    public function handleImageUpload($file,$post)
    {
        //create new image object
        $image = new Image();
        $image->setFile($file);
        $image->setPost($post);
        
        //save file to disk
        $newFilename = rand(1000000, 999999999).rand(1000000, 999999999);
        $image->getFile()->move($image->getUploadDir(), $newFilename);
        $filePath = $image->getUploadDir().'/'.$newFilename;
        
        //check that it is an image
        $is_an_image = $this->resizer->checkFileIsImage($filePath);
        if(!$is_an_image){
            //render an error page 
            return $this->render('InouireMininetBundle:Default:errorPage.html.twig',array(
                'error_level'=> 'bang',
                'error_title'=> 'Impossible d\'envoyer ce fichier',
                'error_message' => 'Le fichier envoyé n\'est pas une image',
                'follow_link' => $this->generateUrl('edit_post',array('post_id' => $image->getPostId() )),
                'follow_link_text' => 'Revenir à l\'édition du post',
            ));
        }
        
        //get image orientation
        $orientation = $this->resizer->getImageOrientation($filePath);
                
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
        $this->em->persist($image);
        $this->em->flush();

        //automatic image rotation and resize (for low disk footprint)
        //TODO handle errors
        $this->resizer->rotateImage($image,$orientation);
        $this->resizer->resizeImage($image, 800);
    }

    
}
