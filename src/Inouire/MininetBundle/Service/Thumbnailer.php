<?php

namespace Inouire\MininetBundle\Service;

use Inouire\MininetBundle\Entity\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Doctrine\ORM\EntityManager;

class Thumbnailer
{
    
    protected $image_dir;
    protected $thumb_dir;
    protected $em;
    
    public function __construct(EntityManager $em, $image_dir)
    {
        $this->em = $em;
        $this->image_dir = __DIR__.'/../../../../'.$image_dir;
        $this->thumb_dir = $this->image_dir.'/thumbnail';
    }
    
    public function generateThumbnail($image)
    {
        // prepare path
        $image_path     = $this->image_dir.'/'.$image->getPath();
        $thumbnail_path = $this->thumb_dir.'/'.$image->getPath();
        
        // set resize options
        $size = new Box(360, 240);
        $mode = ImageInterface::THUMBNAIL_OUTBOUND;
    
        // generate thumbnail
        $imagine = new Imagine();
        $imagine->open($image_path)
                ->thumbnail($size, $mode)
                ->save($thumbnail_path, array('quality' => 95));
        
    }
    
    public function createMissingThumbnails()
    {
        // fetch all images from database
        $all_images = $this->em->getRepository('InouireMininetBundle:Image')->findAll();
        
        // perform check on each database entry
        foreach($all_images as $image){
            if(!file_exists($image->getThumbnailAbsolutePath())){
                $this->generateThumbnail($image);
                echo 'o';
            }else{
                echo '.';
            }
        }
    }

}
