<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Comment;

class PostController extends Controller
{
    
    public function viewAction($post_id){
        return new Response('<html><body>View post action</body></html>');
    }
    
    public function newAction(){
        
        //get current user and user id
        $user = $this->container->get('security.context')->getToken()->getUser();
        $user_id = $user->getId();
        
        //check if this user has a current non-published post
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        $unpublished_post = $post_repo->findOneBy( array('published' => 0,
                                                      'author'=> $user_id),
                                                array(),
                                                1,
                                                0);
        if($unpublished_post == null){
            //if no current post for this user, create one and redirect to it
            $post = new Post();
            $post->setAuthor($user);
            $em->persist($post);
            $em->flush();
            return $this->redirect($this->generateUrl( 'edit_post',array('post_id' => $post->getId() )));            
        }else{
            //else redirect to the current draft post for this user
            return $this->redirect($this->generateUrl( 'edit_post',array('post_id' => $unpublished_post->getId() )));            
        }                
        
    }
   
    public function editAction($post_id){
        
        //get current user and user id
        $user = $this->container->get('security.context')->getToken()->getUser();
        $user_id = $user->getId();
        
        //get corresponding post
        $em = $this->getDoctrine()->getEntityManager();
        $post = $em->getRepository('InouireMininetBundle:Post')->find($post_id);
        
        //check that this post exists and that it belongs to this user
        if($post==null || $post->getAuthor()->getId() != $user_id){
            //TODO properly handle error cases
            return;
        }
        
        return $this->render('InouireMininetBundle:Default:editPost.html.twig',
							  array('post'=> $post));
    }
    
    public function updateContentAction($post_id){
        return new Response('<html><body>Update post content action</body></html>');
    }
    
    public function deleteAction($post_id){
        return new Response('<html><body>Delete post action</body></html>');
    }
}
