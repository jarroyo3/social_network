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
}
