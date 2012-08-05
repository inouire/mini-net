<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\MininetBundle\Entity\Post;
use Inouire\MininetBundle\Entity\Comment;

class DefaultController extends Controller
{
    
    public function rootAction(){
        return $this->redirect($this->generateUrl('home'));
    }
    
    public function homeAction(){
        
        //get entity manager and post repository
        $em = $this->getDoctrine()->getEntityManager();
        $post_repo = $em->getRepository('InouireMininetBundle:Post');
        
        //retrieve last published posts from database
        $post_list = $post_repo->findBy(array('published' => true),
                                        array('date' => 'desc'),
                                        12,
                                        0);

        return $this->render('InouireMininetBundle:Default:home.html.twig',
                              array('post_list'=> $post_list));
    }
    
    public function albumAction(){
        return $this->render('InouireMininetBundle:Default:album.html.twig');
    }
    
    public function testAction(){
        $em = $this->getDoctrine()->getEntityManager();
        
        $test_content="";
        
        //$post = new Post();
        //$post->setContent("Une news avec auteur = edouard, pour voir");
        //$edouard = $em->getRepository('InouireUserBundle:User')->find(1);
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        //$post->setAuthor($user);
        //$em->persist($post);
        
        $post2 = $em->getRepository('InouireMininetBundle:Post')->find(4);
        $test_content.="-> ".$post2->getAuthor()." - ".$post2->getContent();
        //$post2->setContent("Ce week end nous sommes allés à la plage, bronzage garanti");
        
        $comment = new Comment();
        $comment->setContent("Commentaire de test");
        $comment->setAuthor($user);
        $comment->setPost($post2);
        
        $em->persist($comment);
        
        $repository = $em->getRepository('InouireMininetBundle:Post');
        $liste_post = $repository->findAll();
        foreach($liste_post as $post){
            // $article est une instance de Article
            echo $post->getContent();
        }


        $em->flush();
        
        return $this->render('InouireMininetBundle:Default:index.html.twig',array('test_content' => $liste_post));
    }
}
