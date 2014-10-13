<?php

namespace Inouire\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Listener responsible to add roles to user on registration
 */
class RegisterUserListener implements EventSubscriberInterface
{

    protected $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
        
        // if this is the first user to be created, add admin role to the user
        if(count($users)==1){
            $user = $event->getUser();
            $user->addRole('ROLE_ADMIN');
            $this->em->persist($user);
            $this->em->flush();
        }
    }
}
