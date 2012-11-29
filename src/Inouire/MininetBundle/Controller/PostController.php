<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Comment;
use Inouire\MininetBundle\Entity\Image;

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
            $year = $post->getDate()->format('Y');
            $month = $post->getDate()->format('m');
            return $this->redirect($this->generateUrl('posts',array(
                'year' => $year,
                'month' => $month,
            )).'#'.$post->getId()); 
        }

    }
    
   /*
     * Controler for the page to create a new post
     * If a non-published post already exits for the current user,
     * use this one instead of creating a new post
     */
    public function newAction(){
        
        //get current user
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
            //render error page
            return $this->render('InouireMininetBundle:Default:errorPage.html.twig',array(
                'error_level'=> 'bang',
                'error_title'=> 'Post introuvable',
                'error_message' => 'Le post demandé n\'existe pas (ou plus)',
                'follow_link' => $this->generateUrl('home'),
                'follow_link_text' => 'Retourner à la page d\'acceuil',
            ));
        
        }else if( $post->getAuthor() != $user ){
            //the user is not the author-> throw error
            return $this->render('InouireMininetBundle:Default:errorPage.html.twig',array(
                'error_title'=> 'Accès non autorisé',
                'error_message' => 'Vous ne pouvez pas modifier ce post car vous n\'en êtes pas l\'auteur',
                'follow_link' => $this->generateUrl('new_post'),
                'follow_link_text' => 'Ecrire un post',
            )); 
        }else{
            
            //create post form
            $post_form = $this->createFormBuilder($post)
                    ->add('content', 'textarea')
                    ->add('id','hidden')
                    ->getForm();

        
            //create form for image (even if it is hided)
            $image = new Image();
            $image->setPostId($post_id);
            
            $form = $this->createFormBuilder($image)
                ->add('file','file')
                ->add('post_id','hidden')
                ->getForm();
        
            return $this->render('InouireMininetBundle:Post:editPost.html.twig',array(
                'post'=> $post,
                'form' => $form->createView(),
                'post_form' => $post_form->createView(),
            ));
        }
        
    }
    
    
    public function metaAction(){
        
        $post = new Post();

        $form = $this->createFormBuilder($post)
            ->add('content', 'textarea')
            ->add('id','hidden')
            ->getForm();

        if ($request->isMethod('POST')) {
            
            $form->bind($request);

            if ($form->isValid()) {
                // perform some action, such as saving the task to the database
                //get corresponding post
                $post = $this->getPostById($form->getId());
                $post->setContent($form->getContent());
        
                return $this->redirect($this->generateUrl('new_post'));
            }
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
                
                //delete all images related to this post
                foreach( $post->getImages() as $image ){
                    $em->remove($image);
                }
                
                //delete post
                $em->remove($post);
                $response_message = 'post '.$post_id.' has been deleted';
            }else{
                //if first publication, set date to now
                if( $is_published && !$post->getPublished() ){
                    if( strlen($post_content) > 0 || $post->getHasImages() ){
                        $post->touchDate();
                        $post->setPublished($is_published);
                    }
                }else{
                    if( $post_content != $post->getContent() ){
                        $post->touchEditDate();
                    }
                }
               //update content
                $post->setContent($post_content);
                
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
