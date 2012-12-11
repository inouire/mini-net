<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Comment;

class CommentController extends Controller
{
    
    public function postCommentAction(){

        //get content of HTTP POST request
        $request = $this->getRequest();
        $post_id = $request->request->get('post_id');
        $comment_content = $request->request->get('comment');
        
        //get current user (the commenter)
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //get corresponding post
        $em = $this->getDoctrine()->getEntityManager();
        $post = $em->getRepository('InouireMininetBundle:Post')->find($post_id);
        
        //check that this post exists
        if($post==null){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'post '+$post_id+' does not exist'
            ));
        }
        
        //check if the commenter is the author of the post
        if( $post->getAuthor() == $user ){
            $is_author_of_post = 1;
        }else{
            $is_author_of_post = 0;
        }
        
        //create comment
        $comment = new Comment();
        $comment->setContent($comment_content);
        $comment->setAuthor($user);
        $comment->setPost($post);
        
        //persist the new comment to database
        $em->persist($comment);
        $em->flush();
        
        //render json response
        return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
            'status'=> 'ok',
            'message' => 'comment added to post '.$post_id,
            'comment_id' => $comment->getId(),
            'comment_content' => $comment_content,
            'is_author_of_post' => $is_author_of_post
        ));
            
    }
    
    public function updateCommentAction($comment_id){

        //get content of HTTP POST request
        $request = $this->getRequest();
        $comment_content = $request->request->get('comment');
        
        //get requested comment
        $em = $this->getDoctrine()->getEntityManager();
        $comment = $em->getRepository('InouireMininetBundle:Comment')->find($comment_id);
        
        //get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //check that this comment exists
        if($comment==null){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'comment '+$comment_id+' does not exist'
            ));
        }
        
        //check that the user is the author of this comment
        if($user != $comment->getAuthor() ){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'you are not the author of comment '+$comment_id
            ));
        }
        
        //update comment 
        $comment->setContent($comment_content);
        $em->persist($comment);
        $em->flush();
        
        //render json response
        return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
            'status'=> 'ok',
            'message' => 'comment '.$comment_id.' update',
            'comment_id' => $comment_id,
            'comment_content' => $comment_content
        ));
            
    }
    
    public function deleteCommentAction($comment_id){

        //get requested comment
        $em = $this->getDoctrine()->getEntityManager();
        $comment = $em->getRepository('InouireMininetBundle:Comment')->find($comment_id);
        
        //get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //check that this comment exists
        if($comment==null){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'comment '+$comment_id+' does not exist'
            ));
        }
        
        //check that the user is the author of this comment
        if($user != $comment->getAuthor() ){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'you are not the author of comment '+$comment_id
            ));
        }

        //remember the id and the content before deletion
        $comment_id = $comment->getId();
        $comment_content = $comment->getContent();
        
        //remove comment
        $em->remove($comment);
        $em->flush();
        
        //render json response
        return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
            'status'=> 'ok',
            'message' => 'comment '.$comment_id.' deleted',
            'comment_id' => $comment_id,
            'comment_content' => $comment_content
        ));
    }
    
}
