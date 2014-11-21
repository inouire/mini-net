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
        
        // get all invitations + all users
        $invitation_repo = $em->getRepository('InouireUserBundle:Invitation');
        $user_repo       = $em->getRepository('InouireUserBundle:User');   
        $invitations = $invitation_repo->findAll();
        $users       = $user_repo->findAll();
        
        // cross the information to produce a full list (could be done with a join ?)
        $list = $this->mergeUsersAndInvites($users,$invitations);
        
        // create invitation form
        $invitation = new Invitation();
        $form = $this->createSendInvitationForm($invitation);
        
        // render page
        return $this->render('InouireMininetBundle:Admin:invitations.html.twig',array(
            'invitations' => $list,
            'form' => $form->createView()
        ));

    }
    
    private function mergeUsersAndInvites($users, $invitations)
    {
        $list = array();
        
        $used_codes = array();
        
        // list all users and display the ones who are connected to a key
        foreach($users as $user){
            if($user->getInvitation() != null){
                $used_codes[] = $user->getInvitation()->getCode();
                $list[] = array('username'=>$user->getUsername(), 'email'=>$user->getEmail(),'status'=>'registered');
            }
        }
        
        // list all invites and show them that are not in the previous list
        foreach($invitations as $invitation){
            if( ! in_array($invitation->getCode(), $used_codes)){
                $list[] = array('email'=>$invitation->getEmail(),'status'=>'invited');
            }
        }
        return $list;
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
        
        
        // create invitation if it has not already been created for this email
        $form->handleRequest($this->getRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $existing_invite = $em->getRepository('InouireUserBundle:Invitation')->findOneByEmail($invitation->getEmail());
            if (!$existing_invite) {
                $em->persist($invitation);
                $em->flush();
            }
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
