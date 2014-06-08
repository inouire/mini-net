<?php

namespace Inouire\MininetBundle\Service;

use Inouire\MininetBundle\Entity\Image;

class AttachmentLocator
{

    private $images_dir;
    private $images_thumbnail_dir;
    
    public function __construct($image_dir){
        $this->images_dir           = $image_dir.'/';
        $this->images_thumbnail_dir = $image_dir.'/thumbnail/';
    }
    
    /*
     * Get absolute path to the full size version of an image object
     */
    public function getImageAbsolutePath(Image $image)
    {
        return $this->images_dir.$image->getPath();
    }
    
    /*
     * Get absolute path to the full size version of an image object
     */
    public function getImageThumbnailAbsolutePath(Image $image)
    {
        return $this->images_thumbnail_dir.$image->getPath();
    }
    
}
