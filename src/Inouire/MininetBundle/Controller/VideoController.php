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
    
    /**
     * Download video file
     */
    public function getVideoAction(Video $video)
    {
        $video_file=$video->getAbsolutePath();
        $file_type=$video->getType(); 
        
        //prepare response with attachement
        $response = new Response();
        $response->setStatusCode(200);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $video->getName()
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('Cache-Control', 'private, max-age=2592000');//1 month
        
        //set file content
        $response->headers->set('Content-Type',$file_type);
        $response->headers->set('Content-Length',filesize($video_file));
        $response->setContent(file_get_contents($video_file));
        
        return $response;

        //TODO improve this with binary file response ?
        //http://symfony.com/doc/current/components/http_foundation/introduction.html#serving-files
    }
    
    /**
     * Get video thumbnail
     */
    public function getVideoThumbnailAction(Video $video)
    {
        $thumbnail_file=$video->getThumbnailAbsolutePath();
        
        //prepare response
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('Cache-Control', 'private, max-age=2592000');//1 month
        
        //set file content
        $response->headers->set('Content-Type','image/jpeg');
        $response->headers->set('Content-Length',filesize($thumbnail_file));
        $response->setContent(file_get_contents($thumbnail_file));
        
        return $response;
    }

    /**
     * Delete video file
     */
    public function deleteVideoAction(Video $video)
    {
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
            $em = $this->getDoctrine()->getManager();
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
