<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
			//TODO return something
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
			return $this->render('InouireMininetBundle:Default:post-comment.json.twig',
								 array('content' => $comment_content , 'id' => $comment->getId()));
		}else{
			return $this->redirect($this->generateUrl('home'));
		}


	}
	
	public function deleteCommentAction($post_id,$comment_id){
		return $this->redirect($this->generateUrl('home'));
	}
    
}
