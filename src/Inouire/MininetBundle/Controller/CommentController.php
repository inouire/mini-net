<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Comment;

class CommentController extends Controller
{
    
    public function postCommentAction($post_id){
        
        $logger = $this->get('logger');
        $logger->info("POST COMMENT request");
        
        //get content of POST
        $request = $this->getRequest();
        $comment_content = $request->request->get('comment');
        $logger->info("POST content: ".$comment_content);
        
        //get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //get corresponding post
        $em = $this->getDoctrine()->getEntityManager();
        $post = $em->getRepository('InouireMininetBundle:Post')->find($post_id);
        
        //check that this post exists
        if($post==null){
            $logger->info("The associated post id doesn't exist: ".$post_id);
            //TODO properly handle error cases
            return;
        }
        
        //create comment
        $comment = new Comment();
        $comment->setContent($comment_content);
        $comment->setAuthor($user);
        $comment->setPost($post);
        
        //persist the new comment
        $em->persist($comment);
        $em->flush();
        
        if( $request->isXmlHttpRequest() ){
            //TODO build better response
            return new Response('{ "comment" : { "id": '.$comment->getId().', "content": "'.$comment_content.'" } }');
        }else{
            return $this->redirect($this->generateUrl('home'));
        }


    }
    
    public function deleteCommentAction($post_id,$comment_id){
        $logger = $this->get('logger');
        $logger->info("DELETE COMMENT request");
        
        //get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //get requested comment
        $em = $this->getDoctrine()->getEntityManager();
        $comment = $em->getRepository('InouireMininetBundle:Comment')->find($comment_id);
        
        //get corresponding post
        //$post = $em->getRepository('InouireMininetBundle:Post')->find($post_id);
        
        //check that this comment exists
        if($comment==null){
            $logger->info("The associated comment id doesn't exist: ".$comment_id);
            //TODO properly handle error cases
            return;
        }
        
        //check that the user is the author of this comment
        if($user->getId() != $comment->getAuthor()->getId()){
            $logger->info("Error not the author !");
            //TODO build better response
            return new Response('{ error }');
        }

        $id = $comment->getId();
        $content = $comment->getContent();
        
        //remove comment
        $em->remove($comment);
        $em->flush();
        
        if( $this->getRequest()->isXmlHttpRequest() ){
            //TODO build better response
            return new Response('{ "comment" : { "id": '.$id.', "content": "'.$content.'" } }');
        }else{
            return $this->redirect($this->generateUrl('home'));
        }
    }
    
}
