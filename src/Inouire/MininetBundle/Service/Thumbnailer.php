<?php

namespace Inouire\MininetBundle\Service;

use Inouire\MininetBundle\Entity\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

class Thumbnailer
{
    
    protected $image_dir;
    protected $thumb_dir;
    
    public function __construct($image_dir)
    {
        $this->image_dir = __DIR__.'/../../../../'.$image_dir;
        $this->thumb_dir = $this->image_dir.'/thumbnail';
    }
    
    public function generateThumbnail($file_name)
    {
        // prepare path
        $image_path     = $this->image_dir.'/'.$file_name;
        $thumbnail_path = $this->thumb_dir.'/'.$file_name;
        
        // set resize options
        $size = new Box(360, 240);
        $mode = ImageInterface::THUMBNAIL_OUTBOUND;
    
        // generate thumbnail
        $imagine = new Imagine();
        $imagine->open($image_path)
                ->thumbnail($size, $mode)
                ->save($thumbnail_path, array('quality' => 95));
        
    }

}
