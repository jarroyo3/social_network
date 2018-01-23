<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use BackendBundle\Entity\Publication;
use BackendBundle\Entity\Like;
use BackendBundle\Entity\User;

class LikeController extends Controller
{
  public function likeAction($id = null)
  {
      $em = $this->getDoctrine()->getManager();
      $user = $this->getUser();
      $publicationRepo = $em->getRepository('BackendBundle:Publication');
      $publication = $publicationRepo->find($id);
      $like = new Like();
      $like->setUser($user);
      $like->setPublication($publication);
      $em->persist($like);
      $flush = $em->flush();
      $status = '';
      if (null == $flush) {
          $notification = $this->get('app.notification_service');
          $notification->set($publication->getUser(), 'like', $user->getId(), $publication->getId());
        $status = 'Ahora te gusta esta publicación';
      } else {
        $status = 'No se ha podido guardar el like';
      }

      return new Response($status);
  }

  public function unlikeAction($id = null)
  {
      $em = $this->getDoctrine()->getManager();
      $user = $this->getUser();
      $likeRepo = $em->getRepository('BackendBundle:Like');
      $like = $likeRepo->findOneBy([
        'publication' => $id,
        'user' => $user
      ]);
       
      $em->remove($like);
      $flush = $em->flush();
      $status = '';
      
      if (null == $flush) {
        $status = 'Ya no te gusta esta publicación';
      } else {
        $status = 'Error al marcar unlike en la publicación';
      }

      return new Response($status);
  }

  public function likesAction(Request $request, $nickname = null)
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
        $dql = 'SELECT l FROM BackendBundle:Like l WHERE l.user = '. $idUser .' ORDER BY l.id DESC';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $likes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('AppBundle:Like:likes.html.twig', [
            'user' => $user,
            'pagination' => $likes,
        ]);
    }

}
