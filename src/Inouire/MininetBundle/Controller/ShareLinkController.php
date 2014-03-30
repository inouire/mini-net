<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Inouire\MininetBundle\Entity\Image;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\ShareLink;

class ShareLinkController extends Controller
{
    
    /**
     * Create new sharelink on some post attachments
     */
    public function newAction(Post $post){
        
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
    
    public function getImageAction($token, Image $image, $is_thumbnail=false)
    {
        // get sharelink validity status
        $checker = $this->get('inouire.sharelink_checker');
        $sharelink = $checker->checkShareLinkToken($token);
        
        // error if sharelink expired
        if($sharelink == null){
            throw new NotFoundHttpException('Le lien vers cette image a expiré');
        }
        
        // forward to regular controlelr
        return $this->forward('InouireMininetBundle:Image:getImage', array(
            'image'  => $image,
            'is_thumbnail' => $is_thumbnail,
        ));
    }
    
    
}
