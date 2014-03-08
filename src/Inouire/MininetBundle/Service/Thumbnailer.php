<?php

namespace Inouire\MininetBundle\Service;

use Inouire\MininetBundle\Entity\Image;
use Inouire\MininetBundle\Entity\Video;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Doctrine\ORM\EntityManager;
use FFMpeg;

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
    
    public function generateThumbnailFromImage(Image $image)
    {
        // prepare path
        $image_path     = $this->image_dir.'/'.$image->getPath();
        $thumbnail_path = $this->thumb_dir.'/'.$image->getPath();
        
        // generate thumbnail
        $this->generateThumbnail($image_path, $thumbnail_path);        
    }
    
    public function generateThumbnail($image_path, $thumbnail_path)
    {
        // set resize options
        $size = new Box(360, 240);
        $mode = ImageInterface::THUMBNAIL_OUTBOUND;
        
        //generate thumbnail
        $imagine = new Imagine();
        $imagine->open($image_path)
                ->thumbnail($size, $mode)
                ->save($thumbnail_path, array('quality' => 95));
    }
    
    public function generateThumbnailFromVideo(Video $video)
    {
        // prepare path
        $video_path     = __DIR__.'/../../../../web/vid/'.$video->getName();
        $thumbnail_path = __DIR__.'/../../../../web/vid/thumbnail/'.$video->getName().'.jpg';
        
        // extract frame
        $ff = FFMpeg\FFMpeg::create();
        $video = $ff->open($video_path);
        $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3));
        $frame->save($thumbnail_path);
        
        //convert it as a thumbnail
        $this->generateThumbnail($thumbnail_path, $thumbnail_path);
    }
    
    public function createMissingThumbnails()
    {
        // fetch all images from database
        $all_images = $this->em->getRepository('InouireMininetBundle:Image')->findAll();
        
        // perform check on each database entry
        foreach($all_images as $image){
            if(!file_exists($image->getThumbnailAbsolutePath())){
                try{
                    $this->generateThumbnailFromImage($image);
                    echo 'o';
                }catch(\Exception $ex){
                    echo 'x('.$image->getId().')';
                }
            }else{
                echo '.';
            }
        }
    }

}
