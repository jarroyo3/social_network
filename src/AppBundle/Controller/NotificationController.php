<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NotificationController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $dql = 'SELECT n FROM BackendBundle:Notification n WHERE n.user = ' . (int)$user->getId() . ' ORDER BY n.id DESC';
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $notifications = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        $notification = $this->get('app.notification_service');
        $notification->read($user);

        return $this->render('AppBundle:Notification:notification_page.html.twig', [
            'user' => $user,
            'pagination' => $notifications
        ]);
    }

    public function countNotificationAction()
    {
        $em = $this->getDoctrine()->getManager();
        $notificationRepo = $em->getRepository('BackendBundle:Notification');

        $notifications = $notificationRepo->findBy([
            'user' => $this->getUser(),
            'readed' => 0
        ]);

        return new \Symfony\Component\HttpFoundation\Response(count($notifications));
    }
}
