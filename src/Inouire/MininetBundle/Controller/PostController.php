<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Comment;

class PostController extends Controller
{
    
    /*
     * Controler for the page for viewing the content of a post
     */
    public function viewAction($post_id){
        
        //get corresponding post
        $post = $this->getPostById($post_id);
        
        //check that this post exists
        if($post==null){
            //TODO return a more accurate redirection
            return $this->redirect($this->generateUrl('home'));     
        }else{
            return $this->render('InouireMininetBundle:Post:viewPost.html.twig',
                array(
                    'post'=> $post
                )
            );
        }

    }
    
   /*
     * Controler for the page to create a new post
     * If a non-published post already exits for the current user,
     * use this one instead of creating a new post
     */
    public function newAction(){
        
        //get current user and user id
        $user = $this->getCurrentUser();
        
        //check if this user has a current non-published post
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        $unpublished_post = $post_repo->findOneBy(array(
            'published' => 0,
            'author'=> $user->getId()),
            array(),
            1,
            0
        );
        
        if($unpublished_post == null){
            //if no current post for this user, create one and redirect to it
            $post = new Post();
            $post->setAuthor($user);
            $em->persist($post);
            $em->flush();
            return $this->redirect($this->generateUrl('edit_post',array(
                'post_id' => $post->getId()
            )));            
        }else{
            //else redirect to the current draft post for this user
            return $this->redirect($this->generateUrl('edit_post',array(
                'post_id' => $unpublished_post->getId()
            )));            
        }                
        
    }
   
    /*
     * Controler for the page to edit a post
     */
    public function editAction($post_id){
        
        //get current user
        $user = $this->getCurrentUser();
        
        //get corresponding post
        $post = $this->getPostById($post_id);
        
        //check that this post exists, and that it belongs to this user
        if($post==null){
            return $this->redirect($this->generateUrl('home'));
        }else if( $post->getAuthor() != $user ){//the user is not the author-> view only
            return $this->redirect($this->generateUrl('view_post',array(
                'post_id' => $post->getId()
            )));  
        }else{
            return $this->render('InouireMininetBundle:Post:editPost.html.twig',array(
                'post'=> $post
            ));
        }
        
    }
    
    /*
     * Controler for post/delete requests around post
     */
    public function updateAction($post_id){
        
        //get content of POST request
        $request = $this->getRequest();
        $post_content = $request->request->get('content');
        $is_published = (boolean)$request->request->get('published');
        
        //get corresponding post
        $post = $this->getPostById($post_id);
        
        //get current user id
        $user = $this->getCurrentUser();
        
        //get entity manager
        $em = $this->getDoctrine()->getEntityManager();
        
        //check that this post exists and that it belongs to this user
        if($post==null ){
            $response_status = 'error';
            $response_message = 'post '.$post_id.' does not exist';
        }else if( $post->getAuthor() != $user){
            $response_status = 'error';
            $response_message = 'post '.$post_id.' does not belong to you';
        } else {
            
            //get source route to know wether it's an update or delete action
            $routeName = $request->get('_route');
            if( $routeName == 'delete_post'){
                //delete all comments on this post
                foreach( $post->getComments() as $comment ){
                    $em->remove($comment);
                }
                //delete post
                $em->remove($post);
                $response_message = 'post '.$post_id.' has been deleted';
            }else{
               //update content and status
                $post->setContent($post_content);
                $post->setPublished($is_published);
                $response_message = 'post '.$post_id.' has been updated';
            }
            
            //persit changes in the database
            $em->flush();
            $response_status = 'ok';
            
        }
        
        //render json response
        return $this->render('InouireMininetBundle:Post:ajaxResponse.json.twig',array(
            'status'=> $response_status,
            'message' => $response_message
        ));
    }
    
    /**
     * Util function: retrieve a post from database by its id
     */
    public function getPostById($post_id){
        //get entity manager
        $em = $this->getDoctrine()->getEntityManager();
        
        //get the post from the Post repository
        return $em->getRepository('InouireMininetBundle:Post')->find($post_id);
    }
    
    /**
     * Util function: retrieve current user from security context
     */
    public function getCurrentUser(){
        return $this->container->get('security.context')->getToken()->getUser();
    }
    

}
