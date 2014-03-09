<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\PostForm;
use Inouire\MininetBundle\Controller\ImageController;

class PostController extends Controller
{
    
    /*
     * Controler for the page for viewing the content of a post
     */
    public function viewAction($post_id){
        
        //get corresponding post
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('InouireMininetBundle:Post')->find($post_id);
        
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
        $user = $this->container->get('security.context')->getToken()->getUser();

        //check if this user has a current non-published post
        $em = $this->getDoctrine()->getManager();
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
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //get corresponding post
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('InouireMininetBundle:Post')->find($post_id);
        
        //check that this post exists, and that it belongs to this user
        if($post==null){
            //render error page
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_level'=> 'bang',
                'error_title'=> 'Post introuvable',
                'error_message' => 'Le post demandé n\'existe pas (ou plus)',
                'follow_link' => $this->generateUrl('home'),
                'follow_link_text' => 'Retourner à la page d\'acceuil',
            ));
        }else if( $post->getAuthor() != $user ){
            //the user is not the author-> throw an error
            //TODO handle this type of errors with exceptions
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_title'=> 'Accès non autorisé',
                'error_message' => 'Vous ne pouvez pas modifier ce post car vous n\'en êtes pas l\'auteur',
                'follow_link' => $this->generateUrl('new_post'),
                'follow_link_text' => 'Ecrire un post',
            )); 
        }else{
            
            //get all available tags
            $all_tags = $em->getRepository('InouireMininetBundle:Tag')
                           ->findAll();   

            //create post form
            $post_for_form = new PostForm();
            $post_for_form->setContent($post->getContent());
            $post_for_form->setId($post->getId());
            $post_form = $this->getPostForm($post_for_form);

            return $this->render('InouireMininetBundle:Post:editPost.html.twig',array(
                'post'=> $post,
                'post_form' => $post_form->createView(),
                'all_tags' => $all_tags
            ));
        }
        
    }
    

    private function getPostForm(PostForm $post){

        $form = $this->createFormBuilder($post)
            ->add('content', 'textarea',array('required' => false))
            ->add('id','hidden')
            ->add('file','file',array('required' => false))
            ->add('video','file',array('required' => false))
            //TODO use new SF feature for multi button post
            //->add('save', 'submit')
            //->add('publish', 'submit')
            ->getForm();

        return $form;
    }

    /*
     * Controler that handles post submission
     */
    public function updateContentAction(){

        $post_from_form = new PostForm();
        $form = $this->getPostForm($post_from_form);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            
            //get method type (save/publish/udpate/delete) and post id
            $method = $this->getRequest()->request->get('submitButton');
            $post_id = $post_from_form->getId();
            
            //get the post from the Post repository
            $em = $this->getDoctrine()->getManager();
            $post = $em->getRepository('InouireMininetBundle:Post')->find($post_id);
            
            //check that the post exist
            if($post==null){
                return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                    'error_level'=> 'bang',
                    'error_title'=> 'Post introuvable',
                    'error_message' => 'Le post demandé n\'existe pas (ou plus)',
                    'follow_link' => $this->generateUrl('home'),
                    'follow_link_text' => 'Retourner à la page d\'acceuil',
                ));
            }            
            
            //check that the current user own the post
            //TODO handle this type of errors with exceptions
            $user = $this->container->get('security.context')->getToken()->getUser();
            if( $post->getAuthor() != $user ){
                return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                    'error_title'=> 'Accès non autorisé',
                    'error_message' => 'Vous ne pouvez pas modifier ce post car vous n\'en êtes pas l\'auteur',
                    'follow_link' => $this->generateUrl('new_post'),
                    'follow_link_text' => 'Ecrire un post',
                ));
            }
            
            //get image if any
            if( $post_from_form->getFile() != null){
                $image_upload = $this->get('inouire.image_upload');
                $image_upload->handleImageUpload($post_from_form->getFile(),$post);
            }
            
            //get video if any
            if( $post_from_form->getVideo() != null){
                $image_upload = $this->get('inouire.image_upload');
                $image_upload->handleVideoUpload($post_from_form->getVideo(),$post);
            }
            
            //modify post object depending on action
            $had_modification=($post_from_form->getContent() != $post->getContent());
            $post->setContent($post_from_form->getContent());
            $redirect_to = $this->generateUrl('home');
            
            if( $method == 'save'){
                $redirect_to = $this->generateUrl('edit_post',array('post_id' => $post_id));
            }else if($method == 'publish'){
                if( !$post->getPublished() ){
                    if( strlen($post->getContent()) > 0 || $post->getHasImages() ){
                        $post->touchDate();
                        $post->setPublished(true);
                    }
                }
            }else if($method == 'update'){
                if( $had_modification ){
                    $post->touchEditDate();
                }
            }else if($method == 'delete'){
                //delete all comments and images related to this post
                foreach( $post->getComments() as $comment ){
                    $em->remove($comment);
                }
                foreach( $post->getImages() as $image ){
                    $em->remove($image);
                }
                //delete post
                $em->remove($post);
            }else if($method='upload'){
                $redirect_to = $this->generateUrl('edit_post',array('post_id' => $post_id));
            }
             
            //persist changes and redirect to next page (home or post editor)
            $em->flush();
            return $this->redirect($redirect_to);

            return $this->redirect($this->generateUrl('home'));

        }else{
            return $this->render('InouireMininetBundle:Main:errorPage.html.twig',array(
                'error_level'=> 'bang',
                'error_title'=> 'Form invalide',
                'error_message' => 'Arg',
            ));
        }

    
    }
    

}
