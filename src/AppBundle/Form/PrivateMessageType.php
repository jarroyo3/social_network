<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PrivateMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // las $option llegan del controlador, del createForm() 3 parametro
        //dump($options);
        $user = $options['allow_extra_fields'];
        $builder
            ->add('receiver', EntityType::class, [
                'class' => 'BackendBundle:User',
                'query_builder' => function($repository) use ($user){
                    return $repository->getFollowingUsers($user);
                },
                'choice_label' => function($user){
                    return $user->getName() . ' ' . $user->getSurname() . ' - @' . $user->getNick();
                },
                'label' => 'Para',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Mensaje',
                'required' => 'required',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Imagen',
                'required' => false,
                'data_class' => null,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('file', FileType::class, [
                'label' => 'Archivo',
                'required' => false,
                'data_class' => null,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('Publicar', SubmitType::class, [
                'attr' => [
                    'class' => 'form-submit btn btn-success'
                ]
            ]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\PrivateMessage'
        ));
    }
}
