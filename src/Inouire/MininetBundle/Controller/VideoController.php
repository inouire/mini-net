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
    
    public function getVideoAction($video_id){
        
        //get video object
        $em = $this->getDoctrine()->getEntityManager();
        $video = $em->getRepository('InouireMininetBundle:Video')->find($video_id);
        
        //check that this video exists
        if($video==null ){
            //TODO put a better error image file
            $video_file=__DIR__.'/../../../../web/css/icons/exit.png';
            $status_code=404;
        } else {
            $video_file=$video->getAbsolutePath();
            $file_type=$video->getType(); 
            $status_code=200;
        }
        
        //prepare response as attachment
        $response = new Response();
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $video->getName()
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Connection', 'keep-alive');
        $response->setStatusCode($status_code);
        
        //set some cache informations
        $response->setPrivate();
        $response->setMaxAge(172800);//48h
        $response->headers->addCacheControlDirective('must-revalidate', true);
        
        //set file content
        $response->headers->set('Content-Type',$file_type);
        $response->headers->set('Content-Length',filesize($video_file));
        $response->setContent(file_get_contents($video_file));
        
        return $response;

        //TODO improve this with binary file response ?
        //http://symfony.com/doc/current/components/http_foundation/introduction.html#serving-files
    }
    
    public function getVideoThumbnailAction($video_id){
        
        //get video object
        $em = $this->getDoctrine()->getEntityManager();
        $video = $em->getRepository('InouireMininetBundle:Video')->find($video_id);
        
        //check that this video exists
        if($video==null ){
            $thumbnail_file=__DIR__.'/../../../../web/css/icons/exit.png';
            $status_code=404;
        } else {
            $thumbnail_file=$video->getThumbnailAbsolutePath();
            $status_code=200;
        }
        
        //prepare response
        $response = new Response();
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Connection', 'keep-alive');
        $response->setStatusCode($status_code);
        
        //set some cache informations
        $response->setPrivate();
        $response->setMaxAge(172800);//48h
        $response->headers->addCacheControlDirective('must-revalidate', false);
        
        //set file content
        $response->headers->set('Content-Type','image/jpeg');
        $response->headers->set('Content-Length',filesize($thumbnail_file));
        $response->setContent(file_get_contents($thumbnail_file));
        
        return $response;
    }

    public function deleteVideoAction($video_id){

        //get video
        $em = $this->getDoctrine()->getEntityManager();
        $video = $em->getRepository('InouireMininetBundle:Video')->find($video_id);
    
        //check that this video exists and that it belongs to this user
        $user = $this->container->get('security.context')->getToken()->getUser();
        if($video==null ){
            $response_status = 'error';
            $response_message = 'video '.$video_id.' does not exist';
        }else if( $video->getPost()->getAuthor() != $user){
            $response_status = 'error';
            $response_message = 'video '.$video_id.' does not belong to you';
        } else {
            //delete video from disk
            $fs = $this->get('filesystem');
            $fs->remove($video->getAbsolutePath());
            $fs->remove($video->getThumbnailAbsolutePath());
            
            // delete from database
            $em->remove($video);
            $em->flush();
            
            $response_status = 'ok';
            $response_message = 'video '.$video_id.' has been deleted';
        }
        
        //render json response
        return $this->render('InouireMininetBundle:Post:ajaxResponse.json.twig',array(
            'status'=> $response_status,
            'message' => $response_message
        ));
    }
}
