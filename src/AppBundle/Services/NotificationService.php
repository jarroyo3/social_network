<?php

namespace AppBundle\Services;

use BackendBundle\Entity\Notification;

class NotificationService
{
    public $manager;

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function set($user, $type, $typeId, $extra = null)
    {
        $em = $this->manager;
        $status = false;
        
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setTypeId($typeId);
        $notification->setReaded(0);
        $notification->setCreatedAt(new \DateTime('now'));
        $notification->setExtra($extra);
        $em->persist($notification);
        $flush = $em->flush();

        if (null === $flush) {
            $status = true;
        }

        return $status;
    }

    public function read($user)
    {
        $em = $this->manager;
        $notificationRepo = $em->getRepository('BackendBundle:Notification');

        $notifications = $notificationRepo->findBy([
            'user' => $user
        ]);

        foreach ($notifications as $notification) {
            $notification->setReaded(true);
            $em->persist($notification);
        }
        
        return (boolean)$em->flush();
    }
}