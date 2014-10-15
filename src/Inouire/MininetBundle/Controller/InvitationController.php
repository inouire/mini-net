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
        $em = $this->getDoctrine()->getManager();
        
        // create invitation form
        $invitation = new Invitation();
        $form = $this->createSendInvitationForm($invitation);
        
        // check that the invitation has not already been created for this email
        // TODO
        
        // use posted form data to create invitation
        $form->handleRequest($this->getRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($invitation);
            $em->flush();
        }
        
        // send invitation by email to the specified email
        $message = \Swift_Message::newInstance()
                ->setSubject('Invitation à rejoindre mini-net')
                ->setFrom($this->getUser()->getEmail())
                ->setTo($invitation->getEmail())
                ->setBody(
                    $this->renderView(
                        'InouireMininetBundle:Admin:invite_email.txt.twig',
                        array(
                            'admin_name'   => $this->getUser()->getUsername(),
                            'register_url' => $this->generateUrl('fos_user_registration_register',array(),true),
                            'register_code'=> $invitation->getCode()
                        )
                    )
                );
        $this->get('mailer')->send($message);
        
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
