<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use BackendBundle\Entity\User;
use BackendBundle\Entity\PrivateMessage;
use AppBundle\Form\PrivateMessageType;

class PrivateMessageController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $privateMessage = new PrivateMessage();
        $form = $this->createForm(PrivateMessageType::class, $privateMessage, ['allow_extra_fields' => $user]);

        $form->handleRequest($request);
        $status = '';

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // subir imagen
                $file = $form['image']->getData();
                if (!empty($file) && null !== $file) {
                    $ext = $file->guessExtension();
                    if (in_array($ext, ['jpeg', 'jpg', 'png', 'gif'])) {
                        $filename = $user->getId() . time() . '.' . $ext;
                        $file->move('uploads/messages/images', $filename);
                        $privateMessage->setImage($filename);
                    }
                } else {
                    $privateMessage->setImage(null);
                }

                // subir documento
                $doc = $form['file']->getData();
                if (!empty($doc) && null !== $doc) {
                    $ext = $doc->guessExtension();
                    if (in_array($ext, ['doc', 'pdf', 'odt'])) {
                        $docName = $user->getId() . time() . '.' . $ext;
                        $doc->move('uploads/messages/documents', $docName);
                        $privateMessage->setFile($docName);
                    }
                } else {
                    $privateMessage->setFile(null);
                }

                $privateMessage->setEmitter($user);
                $privateMessage->setCreatedAt(new \DateTime('now'));
                $privateMessage->setReaded(false);
                $em->persist($privateMessage);
                $flush = $em->flush();
                if (null === $flush) {
                    $status = 'Has enviado correctamente el mensaje privado';
                } else {
                    $status = 'No se pudo enviar el mensaje privado. Inténtelo de nuevo más tarde.';
                }

            } else {
                $status = 'Ha habido un error al generar tu mensaje privado';
            }

            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('private_message_index');

        } else {
            $status = 'Ha habido un error creando el mensaje privado.';
        }

        $privateMessages = $this->getPrivateMessages($request, 'received');
        $this->setReaded($em, $user);

        return $this->render('AppBundle:PrivateMessage:index.html.twig', [
            'pagination' => $privateMessages,
            'form' => $form->createView()
        ]);
    }

    public function sendedAction(Request $request)
    {
        $privateMessages = $this->getPrivateMessages($request, 'sended');
        return $this->render('AppBundle:PrivateMessage:sended.html.twig', [
            'pagination' => $privateMessages
        ]);
    }

    protected function getPrivateMessages(Request $request, $type = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userId = $user->getId();

        $dql = "";
        if ($type === 'sended') {
            $dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE p.emitter  = $userId ORDER BY p.id";
        } else {
            $dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE p.receiver  = $userId ORDER BY p.id";
        }

        $query = $em->createQuery($dql);
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );
        
        return $pagination;
    }

    public function notReadedAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $privateMessagesRepo = $em->getRepository('BackendBundle:PrivateMessage');
        $countNotReaded = count(
            $privateMessagesRepo->findBy([
                'receiver' => $user,
                'readed' => 0
            ])
        );

        return new Response($countNotReaded);
    }

    private function setReaded($em, $user)
    {
        $privateMessagesRepo = $em->getRepository('BackendBundle:PrivateMessage');
        $privateMessages = $privateMessagesRepo->findBy([
            'receiver' => $user,
            'readed' => 0
        ]);

        foreach ($privateMessages as $pm) {
            $pm->setReaded(true);
            $em->persist($pm);
        }
        
        return (boolean)$em->flush();
    }
}
