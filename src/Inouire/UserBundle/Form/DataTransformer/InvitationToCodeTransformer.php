<?php

namespace Inouire\UserBundle\Form\DataTransformer;

use Inouire\UserBundle\Entity\Invitation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms an Invitation to an invitation code.
 */
class InvitationToCodeTransformer implements DataTransformerInterface
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Invitation) {
            throw new UnexpectedTypeException($value, 'Inouire\UserBundle\Entity\Invitation');
        }

        return $value->getCode();
    }

    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $invitation = $this->entityManager
                           ->getRepository('Inouire\UserBundle\Entity\Invitation')
                           ->findOneBy(array(
                                'code' => $value
                            ));
        $user = $this->entityManager
                     ->getRepository('Inouire\UserBundle\Entity\User')
                     ->findOneBy(array(
                        'invitation' => $invitation
                     )); 
        if($user){
            return null;
        }
        return $invitation;
    }
}
