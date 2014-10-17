<?php

namespace Inouire\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Swift_Mailer;
use \Twig_Environment;

/**
 * Listener responsible to add roles to user on registration
 */
class RegisterUserListener implements EventSubscriberInterface
{

    protected $em;
    
    protected $twig;
    
    protected $swift;
    
    public function __construct(EntityManager $em, Twig_Environment $twig, Swift_Mailer $swift)
    {
        $this->em = $em;
        $this->twig = $twig;
        $this->swift = $swift;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED => 'onFirstUserRegistrationAddAdminRole',
        );
    }

    public function onFirstUserRegistrationAddAdminRole(FilterUserResponseEvent $event)
    {
        // get the list of users (including the one that has just been created)
        $user_repo = $this->em->getRepository('InouireUserBundle:User');   
        $users = $user_repo->findAll();
        
        // get the user who just registered
        $registered_user = $event->getUser();
        
        // if this is the first user to be created, add admin role to the user
        if(count($users)==1){
            $registered_user->addRole('ROLE_ADMIN');
            $this->em->persist($registered_user);
            $this->em->flush();
        }else{ // notify admin that a new account has been created
            
            // find admin user
            foreach($users as $user){// its fine to loop on users as there are very few + this action does not occur often
                foreach($user->getRoles() as $role){
                    if($role == "ROLE_ADMIN"){
                        $admin_user = $user;
                        break;
                    }
                }
            }

            // send an email
            $template_content = $this->twig->loadTemplate('InouireMininetBundle:Admin:registration_notification_email.txt.twig');
            $body = $template_content->render(array('user' => $registered_user));
            $message = \Swift_Message::newInstance()
                ->setSubject('Nouvel utilisateur inscrit: '.$registered_user->getUsername().' / '.$registered_user->getEmail())
                ->setTo($admin_user->getEmail())
                ->setBody($body);
            $this->swift->send($message);
        }
            
    }
}
