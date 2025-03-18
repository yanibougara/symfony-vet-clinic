<?php

namespace App\EventListener;

use App\Entity\Appointment;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class AppointmentListener
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Appointment) {
            return;
        }

        // Set creation date if not already set
        if (null === $entity->getCreatedAt()) {
            $entity->setCreatedAt(new \DateTime());
        }

        // Set current user as assistant if not set and user has the right role
        $user = $this->security->getUser();
        if (null === $entity->getAssistant() && $user !== null && $this->security->isGranted('ROLE_ASSISTANT')) {
            $entity->setAssistant($user);
        }

        // Set default status if not set
        if (null === $entity->getStatus()) {
            $entity->setStatus(Appointment::STATUS_SCHEDULED);
        }

        // Default payment status
        if (null === $entity->isIsPaid()) {
            $entity->setIsPaid(false);
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Appointment) {
            return;
        }

        // Add additional update logic if needed
    }
}