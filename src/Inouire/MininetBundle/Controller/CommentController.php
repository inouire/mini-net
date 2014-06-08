<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Comment;

class CommentController extends Controller
{
    
    /**
     * Add new comment to a post
     */
    public function postCommentAction(){

        // get content of HTTP POST request
        $post_id         = $this->getRequest()->request->get('post_id');
        $comment_content = $this->getRequest()->request->get('comment');
        
        // get current user (the commenter)
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        // get corresponding post
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('InouireMininetBundle:Post')->find($post_id);
        
        // check that this post exists
        if($post==null){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'post '+$post_id+' does not exist'
            ));
        }
        
        // check if the commenter is the author of the post (for correct color display)
        if( $post->getAuthor() == $user ){
            $is_author_of_post = 1;
        }else{
            $is_author_of_post = 0;
        }
        
        // create comment and persist it to database
        $comment = new Comment();
        $comment->setContent($comment_content)
                ->setAuthor($user)
                ->setPost($post);
        $em->persist($comment);
        $em->flush();
        
        // render json response
        return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
            'status'=> 'ok',
            'message' => 'comment added to post '.$post_id,
            'comment_id' => $comment->getId(),
            'comment_content' => $comment_content,
            'is_author_of_post' => $is_author_of_post
        ));
            
    }
    
    /**
     * Update an existing comment
     */
    public function updateCommentAction(Comment $comment){

        // get content of HTTP POST request
        $comment_content = $this->getRequest()->request->get('comment');
        
        // get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        // check that this comment exists
        if($comment==null){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'comment '+$comment_id+' does not exist'
            ));
        }
        
        // check that the user is the author of this comment
        if($user != $comment->getAuthor() ){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'you are not the author of comment '+$comment_id
            ));
        }
        
        // update comment 
        $comment->setContent($comment_content);
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();
        
        // render json response
        return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
            'status'=> 'ok',
            'message' => 'comment '.$comment_id.' update',
            'comment_id' => $comment_id,
            'comment_content' => $comment_content
        ));
            
    }
    
    /**
     * Delete an existing comment
     */
    public function deleteCommentAction(Comment $comment){
        
        // get current user
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        // check that this comment exists
        if($comment==null){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'comment '+$comment_id+' does not exist'
            ));
        }
        
        // check that the user is the author of this comment
        if($user != $comment->getAuthor() ){
            return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
                'status'=> 'error',
                'message' => 'you are not the author of comment '+$comment_id
            ));
        }

        // remember the id and the content before deletion
        $comment_id = $comment->getId();
        $comment_content = $comment->getContent();
        
        // remove comment
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        
        // render json response
        return $this->render('InouireMininetBundle:Default:commentAjaxResponse.json.twig',array(
            'status'=> 'ok',
            'message' => 'comment '.$comment_id.' deleted',
            'comment_id' => $comment_id,
            'comment_content' => $comment_content
        ));
    }
    
}
