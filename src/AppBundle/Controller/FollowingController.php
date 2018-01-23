<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use BackendBundle\Entity\User;
use BackendBundle\Entity\Following;
use AppBundle\Form\RegisterType;
use AppBundle\Form\UserType;

class FollowingController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function followAction(Request $request)
    {
        $user = $this->getUser();
        $followedId = $request->get('followed');

        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(User::class);
        $followed = $userRepo->find($followedId);

        $following = new Following();
        $following->setUser($user);
        $following->setFollowed($followed);
        $em->persist($following);
        $flush = $em->flush();

        if (null == $flush) {
            $notification = $this->get('app.notification_service');
            $notification->set($followed, 'follow', $user->getId());
            $status = 'Ahora estás siguiendo a este usuario';
        } else {
            $status = 'No se pudo seguir a este usuario';
        }

        return new Response($status);
    }

    public function unfollowAction(Request $request)
    {
        $user = $this->getUser();
        $followedId = $request->get('followed');

        $em = $this->getDoctrine()->getManager();
        $followRepo = $em->getRepository(Following::class);
        $followed = $followRepo->findOneBy(['user' => $user, 'followed' => $followedId]);
        if ($followed) {
            $em->remove($followed);
            $flush = $em->flush();
    
            if (null == $flush) {
                $status = 'Ahora has dejado de seguir a este usuario';
            } else {
                $status = 'No se pudo dejar de seguir a este usuario';
            }
    
            return new Response($status);
        }

        return new Response('Algo fue mal en la petición unfollow');
    }

    public function followingAction(Request $request, $nickname = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (null !== $nickname) {
            $userRepo = $em->getRepository('BackendBundle:User');
            $user = $userRepo->findOneBy([
                'nick' => $nickname
            ]);
                
        } else {
            $user = $this->getUser();
        }
            
        
        if (empty($user) || !is_object($user)) {
            return $this->redirect($this->generateUrl('home_publications'));
        }

        $idUser = $user->getId();
        $dql = 'SELECT f FROM BackendBundle:Following f WHERE f.user = '. $idUser .' ORDER BY f.id DESC';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $following = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('AppBundle:Following:following.html.twig', [
            'profile_user' => $user,
            'pagination' => $following,
            'type' => 'following'
        ]);
    }

    public function followedAction(Request $request, $nickname = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (null !== $nickname) {
            $userRepo = $em->getRepository('BackendBundle:User');
            $user = $userRepo->findOneBy([
                'nick' => $nickname
            ]);
                
        } else {
            $user = $this->getUser();
        }
            
        
        if (empty($user) || !is_object($user)) {
            return $this->redirect($this->generateUrl('home_publications'));
        }

        $idUser = $user->getId();
        $dql = 'SELECT f FROM BackendBundle:Following f WHERE f.followed = '. $idUser .' ORDER BY f.id DESC';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $followed = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('AppBundle:Following:following.html.twig', [
            'profile_user' => $user,
            'pagination' => $followed,
            'type' => 'followed'
        ]);
    }
}
