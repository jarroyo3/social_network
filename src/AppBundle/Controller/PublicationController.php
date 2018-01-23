<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Form\PublicationType;
use BackendBundle\Entity\Publication;

class PublicationController extends Controller
{
  private $session;

  public function __construct()
  {
    $this->session = new Session();
  }
  
  public function indexAction(Request $request)
  {
      $em = $this->getDoctrine()->getManager();
      $publication = new Publication();
      $form = $this->createForm(PublicationType::class, $publication);
      $status = '';
      $user = $this->getUser();
      $form->handleRequest($request);
      if ($form->isSubmitted()) {
        if ($form->isValid()) {
          // subir imagen
          $file = $form['image']->getData();
          if (!empty($file) && null !== $file) {
            $ext = $file->guessExtension();
            if (in_array($ext, ['jpeg', 'jpg', 'png', 'gif'])) {
              $filename = $user->getId() . time() . '.' . $ext;
              $file->move('uploads/publications/images', $filename);
              $publication->setImage($filename);
            }
          } else {
            $publication->setImage(null);
          }

          // subir documento
          $doc = $form['document']->getData();
          if (!empty($doc) && null !== $doc) {
            $ext = $doc->guessExtension();
            if (in_array($ext, ['doc', 'pdf', 'odt'])) {
              $docName = $user->getId() . time() . '.' . $ext;
              $doc->move('uploads/publications/documents', $docName);
              $publication->setDocument($docName);
            }
          } else {
            $publication->setDocument(null);
          }

          $publication->setUser($user);
          $publication->setCreatedAt(new \DateTime('now'));
          $em->persist($publication);
          $flush = $em->flush();
          if (null === $flush) {
            $status = 'Tu publicación se ha creado con éxito';
          } else {
            $status = 'No se pudo guardar tu publicación. Inténtelo de nuevo más tarde.';
          }

        } else {
          $status = 'Ha habido un error al generar tu publicación';
        }

        $this->session->getFlashBag()->add('status', $status);
        return $this->redirectToRoute('home_publications');
      }

      $publications = $this->getPublications($request);

      return $this->render('AppBundle:Publication:home.html.twig', [
        'form' => $form->createView(),
        'pagination' => $publications
      ]);
  }

  public function getPublications($request)
  {
      $em = $this->getDoctrine()->getManager();
      $user = $this->getUser();
      $publicationsRepo = $em->getRepository('BackendBundle:Publication');
      $followingRepo = $em->getRepository('BackendBundle:Following');
      
      //'SELECT text FROM publications WHERE user_id = :user OR user_id IN (SELECT followed FROM following where user = :user)';
      $following = $followingRepo->findBy(['user' => $user]);
      $followingArray = [];
      
      foreach ($following as $follow) {
        $followingArray[] = $follow->getFollowed();
      }

      $query = $publicationsRepo->createQueryBuilder('p')
        ->where('p.user = (:user_id) OR p.user IN (:following)')
        ->setParameter('user_id', $user->getId())
        ->setParameter('following', $followingArray)
        ->orderBy('p.createdAt', 'DESC')
        ->getQuery();

      $paginator = $this->get('knp_paginator');
      $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        5
      );

      return $pagination;
  }

  public function removePublicationAction(Request $request, $id = null)
  {
      $status = '';
      if (null != $id) {
        $em = $this->getDoctrine()->getManager();
        $publicationsRepo = $em->getRepository('BackendBundle:Publication');
        $publication = $publicationsRepo->find($id);
        $user = $this->getUser();
        if ($user->getId() == $publication->getUser()->getId())
          $em->remove($publication);
          $flush = $em->flush();
          if (null == $flush) {
            $status = 'Tu publicación se ha borrado correctamente';
          } else {
            $status = 'Tu publicación no pudo borrarse';
          }
      }

      return new Response($status);
  }
}
