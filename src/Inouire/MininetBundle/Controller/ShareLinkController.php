<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
        
        // check that there is not already a share link on this post created the same day
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
    public function getAction($token){
        
        // try to find this link
        $em = $this->getDoctrine()->getManager();
        $sharelink_repo = $em->getRepository('InouireMininetBundle:ShareLink');
        $sharelink = $sharelink_repo->findOneBy(array('token' => $token));
        
        
        if($sharelink == null){
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_title'=> 'No share link found for token '.$token,
                'error_message' => 'This access is public',
                'follow_link' => $this->generateUrl('home'),
                'follow_link_text' => 'Retour à l\'accueil',
            ));

        }else{
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_title'=> 'Public access to share link '.$token,
                'error_message' => 'This access is public',
                'follow_link' => $this->generateUrl('home'),
                'follow_link_text' => 'Retour à l\'accueil',
            ));
        }
        
    }
    
}
