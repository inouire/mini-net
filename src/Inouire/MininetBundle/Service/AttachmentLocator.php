<?php

namespace Inouire\MininetBundle\Service;

use Inouire\MininetBundle\Entity\Image;
use Inouire\MininetBundle\Entity\Video;

class AttachmentLocator
{

    protected $images_dir;
    protected $images_thumbnail_dir;
    
    protected $video_dir;
    protected $video_thumbnail_dir;
    
    public function __construct($image_dir, $video_dir)
    {
        $this->images_dir           = $image_dir.'/';
        $this->images_thumbnail_dir = $image_dir.'/thumbnail/';
        
        $this->video_dir            = $video_dir.'/';
        $this->video_thumbnail_dir  = $video_dir.'/thumbnail/';
    }
    
    public function getImageRootDir()
    {
        return $this->images_dir;
    }
    
    public function getVideoRootDir()
    {
        return $this->video_dir;
    }
    
    /*
     * Get absolute path to the full size version of an image attachment
     */
    public function getImageAbsolutePath(Image $image)
    {
        return $this->images_dir.$image->getPath();
    }
    
    /*
     * Get absolute path to the thumbnail version of an image attachment
     */
    public function getImageThumbnailAbsolutePath(Image $image)
    {
        return $this->images_thumbnail_dir.$image->getPath();
    }
    
    
    /*
     * Get absolute path to a video attachment
     */
    public function getVideoAbsolutePath(Video $video)
    {
        return $this->video_dir.$video->getName();
    }
    
    /*
     * Get absolute path to the thumbnail version of a video attachment
     */
    public function getVideoThumbnailAbsolutePath(Video $video)
    {
        return $this->video_thumbnail_dir.$video->getName().'.jpg';
    }
    
    /*
     * Get absolute path to the thumbnail watermark for videos
     */
    public function getVideoWatermarkAbsolutePath()
    {
        return $this->video_thumbnail_dir.'watermark.png';
    }
}
