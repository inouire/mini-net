<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Inouire\UserBundle\Entity\Invitation;

class InvitationController extends Controller
{
    /**
     * View the list of current invitations
     */
    public function listAction()
    {
        
        $em = $this->getDoctrine()->getManager();
        
        // get all invitations
        $invitation_repo = $em->getRepository('InouireUserBundle:Invitation');   
        $invitations = $invitation_repo->findAll();
        
        // create invitation form
        $invitation = new Invitation();
        $form = $this->createSendInvitationForm($invitation);
        
        // render page
        return $this->render('InouireMininetBundle:Admin:invitations.html.twig',array(
            'invitations' => $invitations,
            'form' => $form->createView()
        ));

    }
    
    /**
     * Send an invite to a specific email
     */
    public function inviteAction()
    {
        // create invitation form
        $invitation = new Invitation();
        $form = $this->createSendInvitationForm($invitation);
        
        // use posted form data to create invitation
        $form->handleRequest($this->getRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($invitation);
            $em->flush();
        }
        
        // send invitation by email to the specified email
        // TODO
        
        // redirect to invitations list
        return $this->redirect($this->generateUrl('admin_invitations'));
    }
    
    /**
     * Build form for invitations sending
     */
    private function createSendInvitationForm(Invitation $invitation)
    {
        $form = $this->createFormBuilder($invitation)
                     ->setAction($this->generateUrl('admin_invite'))
                     ->setMethod('POST')
                     ->add('email', 'email')
                     ->add('send', 'submit', array('label' => 'Inviter'))
                     ->getForm();
        return $form;
    }

    
    
}
