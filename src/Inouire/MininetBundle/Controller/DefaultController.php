<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function rootAction(){
		return $this->redirect($this->generateUrl('home'));
	}
	
    public function homeAction(){
        return $this->render('InouireMininetBundle:Default:home.html.twig');
    }
    
	public function albumAction(){
        return $this->render('InouireMininetBundle:Default:album.html.twig');
    }
}
