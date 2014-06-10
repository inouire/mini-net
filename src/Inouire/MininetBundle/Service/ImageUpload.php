<?php

namespace Inouire\MininetBundle\Service;

use Doctrine\ORM\EntityManager;
use Inouire\MininetBundle\Service\ImageResize;
use Inouire\MininetBundle\Service\Thumbnailer;
use Inouire\MininetBundle\Entity\Image;
use Inouire\MininetBundle\Entity\Video;
use Inouire\MininetBundle\Service\AttachmentLocator;

class ImageUpload
{

    protected $em;
    protected $resizer;
    protected $thumbnailer;
    protected $locator;
    
    public function __construct(EntityManager $em, ImageResize $resizer, Thumbnailer $thumbnailer, AttachmentLocator $locator)
    {
        $this->em = $em;
        $this->resizer = $resizer;
        $this->thumbnailer = $thumbnailer;
        $this->locator = $locator;
    }
    
    public function handleImageUpload($file,$post)
    {
        
        // try to guess the extension
        $extension = $file->guessExtension();
        if (!$extension) {// extension cannot be guessed
            $extension = 'bin';
        }
        $image_filename = rand(1000000, 999999999).rand(1000000, 999999999).'.'.$extension;
        
        // check that it is an image
        $is_an_image = $this->resizer->checkFileIsImage($file);
        if(!$is_an_image){
            throw new \Exception('Le fichier envoyé n\'est pas reconnu comme une image');
        }

        // create image object
        $image = new Image();
        $image->setPost($post);
        $image->setPath($image_filename);
        $image_path = $this->locator->getImageAbsolutePath($image);
        $root_dir = $this->locator->getImageRootDir();
        
        // move uploaded file to upload dir
        $file->move($root_dir, $image_filename);
        
        // automatic image rotation/resize/thumbnails
        $orientation = $this->resizer->getImageOrientation($image);
        $this->resizer->rotateImage($image,$orientation);
        $this->resizer->resizeImage($image, 800);
        $this->thumbnailer->generateThumbnailFromImage($image);
        
        // save image object to database
        $this->em->persist($image);
        $this->em->flush();
        
    }

    public function handleVideoUpload($file, $post)
    {
        // get information
        $original_name = $file->getClientOriginalName();
        $original_mimetype = $file->getClientMimeType();
        $extension = substr($original_name, -3); 
        
        // build name
        $random_filename = rand(1000000, 999999999).rand(1000000, 999999999).'.'.$extension;
        
        // TODO
        // check that the name has not already been taken
        // check that it is a video
        
        // create video object
        $video = new Video();
        $video->setPost($post);
        $video->setName($random_filename);
        $video->setType($original_mimetype);
        $video_path = $this->locator->getVideoAbsolutePath($video);
        $root_dir = $this->locator->getVideoRootDir();
        
        // move uploaded file to upload dir
        $file->move($root_dir, $random_filename);
        
        // generate thumbnail
        $this->thumbnailer->generateThumbnailFromVideo($video);
        
        // save video object to database
        $this->em->persist($video);
        $this->em->flush();
        
    }
    
}
