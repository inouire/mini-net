<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\ShareLink;
use Inouire\MininetBundle\Entity\Image;
use Inouire\MininetBundle\Entity\Video;

class ShareLinkController extends Controller
{
    
    /**
     * Create new sharelink on some post attachments
     */
    public function newAction(Post $post)
    {
        //get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        // check that there is not already a share link on this post+user created the same day
        $em = $this->getDoctrine()->getManager();
        $sharelink_repo = $em->getRepository('InouireMininetBundle:ShareLink');
        $existing_sharelink = $sharelink_repo->findOneBy(array(
            'post' => $post,
            'creation_date' => new \DateTime(),
            'author'=> $user)
        );
        
        if($existing_sharelink == null){
            // create new link
            $fresh_link = new ShareLink();
            $fresh_link->setAuthor($user);
            $post->addShareLink($fresh_link);
            
            // persist new link
            $em = $this->getDoctrine()->getManager();
            $em->persist($fresh_link);
            $em->flush();
            
            $sharelink = $fresh_link;
        }else{
            $sharelink = $existing_sharelink;
        }
        
        return $this->render('InouireMininetBundle:Main:createShareLink.html.twig',array(
            'sharelink' => $sharelink
        )); 
    }
    
    /**
     * Public acess to a sharelink
     */
    public function getAction($token)
    {
        // get sharelink validity status
        $checker = $this->get('inouire.sharelink_checker');
        $sharelink = $checker->checkShareLinkToken($token);
        
        if($sharelink == null){
            // sharelink has expired
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_title'=> 'Lien expiré',
                'error_message' => 'Le lien public que vous avez demandé a expiré',
                'follow_link' => $this->generateUrl('home'),
                'follow_link_text' => 'Retourner à la page d\'acceuil',
            ));
        }else{
            // sharelink is valid, render public link
            return $this->render('InouireMininetBundle:Main:getShareLink.html.twig',array(
                'sharelink' => $sharelink
            ));
        }        
    }
    
    /**
     * Get image file through sharelink
     */
    public function getImageAction($token, Image $image, $is_thumbnail=false)
    {
        // get sharelink validity status
        $checker = $this->get('inouire.sharelink_checker');
        $sharelink = $checker->checkShareLinkToken($token);
        
        // error if sharelink expired
        if($sharelink == null){
            throw new NotFoundHttpException('Le lien vers cette image a expiré');
        }
        
        // check that the requested image is part of the sharelink
        $authorized_images = $sharelink->getPost()->getImages();
        $image_id = $image->getId();
        $is_authorized = false;
        foreach($authorized_images as $authorized_image){
            if($authorized_image->getId() == $image_id){
                $is_authorized = true;
            }
        }
        if(!$is_authorized){
            throw new NotFoundHttpException('Le lien vers cette image n\'existe pas');
        }
        
        // forward to regular controlelr
        return $this->forward('InouireMininetBundle:Image:getImage', array(
            'image'  => $image,
            'is_thumbnail' => $is_thumbnail,
        ));
    }
    
    /**
     * Get video file through sharelink
     */
    public function getVideoAction($token, Video $video)
    {
        // get sharelink validity status
        $checker = $this->get('inouire.sharelink_checker');
        $sharelink = $checker->checkShareLinkToken($token);
        
        // error if sharelink expired
        if($sharelink == null){
            throw new NotFoundHttpException('Le lien vers cette vidéo a expiré');
        }
        
        // check that the requested video is part of the sharelink
        $authorized_videos = $sharelink->getPost()->getVideos();
        $video_id = $video->getId();
        $is_authorized = false;
        foreach($authorized_videos as $authorized_video){
            if($authorized_video->getId() == $video_id){
                $is_authorized = true;
            }
        }
        if(!$is_authorized){
            throw new NotFoundHttpException('Le lien vers cette vidéo n\'existe pas');
        }
        
        // forward to regular controlelr
        return $this->forward('InouireMininetBundle:Video:getVideo', array(
            'video'  => $video,
        ));
    }
    
    /**
     * Get video thumbnail through sharelink
     */
    public function getVideoThumbnailAction($token, Video $video)
    {
        // get sharelink validity status
        $checker = $this->get('inouire.sharelink_checker');
        $sharelink = $checker->checkShareLinkToken($token);
        
        // error if sharelink expired
        if($sharelink == null){
            throw new NotFoundHttpException('Le lien vers cet aperçu de vidéo a expiré');
        }
        
        // check that the requested video is part of the sharelink
        $authorized_videos = $sharelink->getPost()->getVideos();
        $video_id = $video->getId();
        $is_authorized = false;
        foreach($authorized_videos as $authorized_video){
            if($authorized_video->getId() == $video_id){
                $is_authorized = true;
            }
        }
        if(!$is_authorized){
            throw new NotFoundHttpException('Le lien vers cette aperçu de vidéo n\'existe pas');
        }
        
        // forward to regular controlelr
        return $this->forward('InouireMininetBundle:Video:getVideoThumbnail', array(
            'video'  => $video,
        ));
    }
}
