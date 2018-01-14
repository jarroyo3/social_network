<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use BackendBundle\Entity\User;
use AppBundle\Form\RegisterType;
use AppBundle\Form\UserType;

class UserController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function loginAction(Request $request)
    {
        if (is_object($this->getuser())) {
            return $this->redirect('/home');
        }
        
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('AppBundle:User:login.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error
        ]);
    }

    public function registerAction(Request $request)
    {
        if (is_object($this->getuser())) {
            return $this->redirect('/home');
        }

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        
        $form->handleRequest($request);
        $status = "";
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $userRepo = $em->getRepository(User::class);
                $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email = :email OR u.nick = :nick')
                    ->setParameter('email', $form->get('email')->getData())
                    ->setParameter('nick', $form->get('nick')->getData());
                
                $userIsset = $query->getResult();
                // crea el usuario si count = 0
                if (count($userIsset) == 0) {
                    $factory = $this->get('security.encoder_factory');
                    $encoder = $factory->getEncoder($user);
                    $password = $encoder->encodePassword($form->get('password')->getData(), $user->getSalt());

                    $user->setPassword($password);
                    $user->setRole("ROLE_USER");
                    $user->setImage(null);
                    $em->persist($user);
                    $flush = $em->flush();
                    if ($flush == null) {
                        $status = sprintf("Te has registrado correctamente");
                        $this->session->getFlashBag()->add('status', $status);
                        return $this->redirect('/register');
                    } else {
                        $status = sprintf("Ha habido un problema al procesar tu registro.");
                    }

                }else {
                    $status = sprintf("El correo electrónico o nick ya existen");
                }

            } else {
                $status = sprintf("No te has registrado correctamente.");
            }
            
            $this->session->getFlashBag()->add('status', $status);
        }
        
        return $this->render('AppBundle:User:register.html.twig', [
          'form' => $form->createView()
        ]);
    }

    public function nickTestAction(Request $request)
    {
        $nick = $request->get('nick');
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository(User::class);
        $userIsset = $userRepo->findOneBy([
            'nick' => $nick
        ]);
        
        $nickUsed = 'unused';
        if (count($userIsset) > 0 && is_object($userIsset)) {
            $nickUsed = 'used';
        }
        return new Response($nickUsed);
    }

    public function editUserAction(Request $request)
    {
        if (null === $this->getuser()) {
            return $this->redirect('/home');
        }

        $user = $this->getUser();
        $userImage = $user->getImage();
        $form = $this->createForm(UserType::class, $this->getUser());

        $form->handleRequest($request);
        $status = "";
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $userRepo = $em->getRepository(User::class);
                $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email = :email OR u.nick = :nick')
                    ->setParameter('email', $form->get('email')->getData())
                    ->setParameter('nick', $form->get('nick')->getData());
                
                $userIsset = $query->getResult();
                // crea el usuario si count = 0
                $check = count($userIsset[0]) == 0 || 
                    ($user->getEmail() == $userIsset[0]->getEmail()) && 
                    ($user->getNick() == $userIsset[0]->getNick());

                if ($check) {
                    
                    // uploading file...
                    $file = $form['image']->getData();
                    $userImage = null;
                    if ($file && null != $file) {
                        if (in_array($file->guessExtension(), ['jpg','jpeg', 'gif', 'png'])) {
                            $filename = $user->getId().time() . '.' . $file->guessExtension();
                            $file->move('uploads/users', $filename);
                            $userImage = $filename;
                        }
                    }

                    $user->setImage($userImage);
                    $em->persist($user);
                    $flush = $em->flush();
                    if ($flush == null) {
                        $status = sprintf("Tus datos se han actualizado correctamente.");
                    } else {
                        $status = sprintf("Ha habido un problema al modificar tus datos.");
                    }

                }else {
                    $status = sprintf("El usuario o contraseña ya existen");
                }

            } else {
                $status = sprintf("No se han guardado los datos.");
            }
            
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirect('my-data');
        }

        return $this->render('AppBundle:User:edit_user.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function usersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT u FROM BackendBundle:User u ORDER BY u.id';
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        
        return $this->render('AppBundle:User:users.html.twig', [
            'pagination' => $pagination
        ]);
    }

    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $search = $request->query->get('search', null);

        if (null == $search) {
            return $this->redirect($this->generateURL('home_publications'));
        }

        $dql = 'SELECT u FROM BackendBundle:User u 
            WHERE u.name LIKE :search 
            OR u.surname LIKE :search 
            OR u.nick LIKE :search ORDER BY u.id';
        $query = $em->createQuery($dql)->setParameter('search', "%$search%");

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        
        return $this->render('AppBundle:User:users.html.twig', [
            'pagination' => $pagination
        ]);
    }
}
