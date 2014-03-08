<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Video;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class VideoController extends Controller
{
    
    public function getVideoAction($video_id, $is_thumbnail=false){
        
        //get video object
        $em = $this->getDoctrine()->getEntityManager();
        $video = $em->getRepository('InouireMininetBundle:Video')->find($video_id);
        
        //check that this video exists
        if($video==null ){
            //TODO put a better error image file
            $video_file=__DIR__.'/../../../../web/css/icons/exit.png';
            $status_code=404;
        } else {
            if($is_thumbnail){
                $video_file=$video->getThumbnailAbsolutePath();
                $file_type='image/jpeg'; 
            }else{
                $video_file=$video->getAbsolutePath();
                $file_type='video/mp4'; 
            }
            $status_code=200;
        }
        
        //prepare response
        $response = new Response();
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Connection', 'keep-alive');
        $response->setStatusCode($status_code);
        
        //set some cache informations
        $response->setPrivate();
        $response->setMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        
        //set file content
        $response->headers->set('Content-Type',$file_type);
        $response->headers->set('Content-Length',filesize($video_file));
        $response->setContent(file_get_contents($video_file));
        
        return $response;
        
        //http://symfony.com/doc/current/components/http_foundation/introduction.html#serving-files
    }
    
}
