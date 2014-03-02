<?php

namespace Inouire\MininetBundle\Service;

use Inouire\MininetBundle\Entity\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageResize
{

    public function __construct(){
        
    }
    
    /*
     * Check that the file is an image
     */
    public function checkFileIsImage($filePath)
    {
        try{
            $imagine = new Imagine();
            $image = $imagine->open($filePath);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }
    
    /*
     * Get image orientation
     */
    public function getImageOrientation($filePath)
    {
        //default: normal orientation
        $orientation = 1;
        try{
            //get IFDO.Orientation from exif data
            $exif = exif_read_data($filePath, 0, true);
            $orientation = $exif['IFD0']['Orientation'];
        }catch(\Exception $e){
            $orientation = 1;
        }
        return $orientation;
    }
    
    /*
     * Rotate an image depending on its orientation
     */
    public function rotateImage($image,$orientation)
    {
        $rotation_angles= array(
            1 =>   0,
            3 => 180,
            6 =>  90,
            8 => -90,
        );
        
        //compute rotation angle
        $angle = $rotation_angles[$orientation];
        
        if($angle != 0){
            //open image
            $imagine = new Imagine();
            $image_to_rotate = $imagine->open($image->getAbsolutePath());
               
            //rotate it and save it
            $save_options = array('quality' => 100);
            $image_to_rotate->rotate($angle)
                            ->save($image->getAbsolutePath(),$save_options);
        }
    }
    
    /*
     * Resize an image based on a maximum height
     */
    public function resizeImage($image, $max_height)
    {
        //open image
        $imagine = new Imagine();
        $image_to_resize = $imagine->open($image->getAbsolutePath());
        
        //get actual size
        $actual_size = $image_to_resize->getSize();
        
        //if necessary, resize to a given max height (800 for example), and save to disk with the same name
        if( $actual_size->getHeight() > $max_height ){
            $new_size = $actual_size->heighten($max_height);
            $save_options = array('quality' => 90);
            $image_to_resize->resize($new_size)
                            ->save($image->getAbsolutePath(),$save_options);
        }
    }
    
    
}
