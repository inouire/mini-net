<?php

namespace Inouire\MininetBundle\Service;

use Inouire\MininetBundle\Entity\Image;
use Inouire\MininetBundle\Entity\Video;
use Imagine\Gd\Imagine;
use Imagine\Image\Point;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Doctrine\ORM\EntityManager;
use Inouire\MininetBundle\Service\AttachmentLocator;
use FFMpeg;

class Thumbnailer
{
    
    protected $em;
    protected $locator;
    
    public function __construct(EntityManager $em, AttachmentLocator $locator)
    {
        $this->em = $em;
        $this->locator = $locator;
    }
    
    public function generateThumbnailFromImage(Image $image)
    {
        // Retrive absolute paths
        $image_path     = $this->locator->getImageAbsolutePath($image);
        $thumbnail_path = $this->locator->getImageThumbnailAbsolutePath($image);
        
        // Generate thumbnail
        $this->generateThumbnail($image_path, $thumbnail_path);        
    }
    
    private function generateThumbnail($image_path, $thumbnail_path)
    {
        // Set resize options
        $size = new Box(360, 240);
        $mode = ImageInterface::THUMBNAIL_OUTBOUND;
        
        // Create thumbnail
        $imagine = new Imagine();
        $imagine->open($image_path)
                ->thumbnail($size, $mode)
                ->save($thumbnail_path, array('quality' => 95));
    }
    
    public function generateThumbnailFromVideo(Video $video)
    {
        // Retrive absolute paths
        $video_path     = $this->locator->getVideoAbsolutePath($video);
        $thumbnail_path = $this->locator->getVideoThumbnailAbsolutePath($video);
        $watermark_path = $this->locator->getVideoWatermarkAbsolutePath();
        
        // Extract frame for thumbnail at t=3sec
        $ff = FFMpeg\FFMpeg::create();
        $video = $ff->open($video_path);
        $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(3));
        $frame->save($thumbnail_path);
        
        // Convert it as a thumbnail
        $this->generateThumbnail($thumbnail_path, $thumbnail_path);
        
        // Add video watermark
        $imagine = new Imagine();
        $watermark = $imagine->open($watermark_path);
        $image     = $imagine->open($thumbnail_path);
        $topLeft = new Point(0,0);
        $image->paste($watermark, $topLeft)
              ->save($thumbnail_path, array('quality' => 95));
    }
    
    public function createMissingThumbnails()
    {
        // Fetch all images from database
        $all_images = $this->em->getRepository('InouireMininetBundle:Image')->findAll();
        
        // Perform check on each database entry
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
