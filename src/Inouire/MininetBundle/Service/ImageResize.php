<?php

namespace Inouire\MininetBundle\Service;

use Inouire\MininetBundle\Entity\Image;
use Imagine\Gd\Imagine;
use Inouire\MininetBundle\Service\AttachmentLocator;

class ImageResize
{

    protected $locator;
    
    public function __construct(AttachmentLocator $locator){
        $this->locator = $locator;
    }
    
    /*
     * Check that the file is an image
     */
    public function checkFileIsImage($filePath)
    {
        try{
            $imagine = new Imagine();
            $imagine->open($filePath);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }
    
    /*
     * Get image orientation
     */
    public function getImageOrientation(Image $image)
    {
        // get path
        $image_path = $this->locator->getImageAbsolutePath($image);
        
        // detect orientation
        // default: normal orientation
        $orientation = 1;
        try{
            //get IFDO.Orientation from exif data
            $exif = exif_read_data($image_path, 0, true);
            $orientation = $exif['IFD0']['Orientation'];
        }catch(\Exception $e){
            $orientation = 1;
        }
        return $orientation;
    }
    
    /*
     * Rotate an image depending on its orientation
     */
    public function rotateImage(Image $image,$orientation)
    {
        // get path
        $image_path = $this->locator->getImageAbsolutePath($image);
        
        //compute rotation angle
        $rotation_angles= array(
            1 =>   0,
            3 => 180,
            6 =>  90,
            8 => -90,
        );
        $angle = $rotation_angles[$orientation];
        
        if($angle != 0){
            //open image
            $imagine = new Imagine();
            $image_to_rotate = $imagine->open($image_path);
               
            //rotate it and save it
            $save_options = array('quality' => 100);
            $image_to_rotate->rotate($angle)
                            ->save($image_path,$save_options);
        }
    }
    
    /*
     * Resize an image based on a maximum height
     */
    public function resizeImage(Image $image, $max_height)
    {
        // get path
        $image_path = $this->locator->getImageAbsolutePath($image);
        
        //open image
        $imagine = new Imagine();
        $image_to_resize = $imagine->open($image_path);
        
        //get actual size
        $actual_size = $image_to_resize->getSize();
        
        //if necessary, resize to a given max height (800 for example), and save to disk with the same name
        if( $actual_size->getHeight() > $max_height ){
            $new_size = $actual_size->heighten($max_height);
            $save_options = array('quality' => 90);
            $image_to_resize->resize($new_size)
                            ->save($image_path,$save_options);
        }
    }
    
    
}
