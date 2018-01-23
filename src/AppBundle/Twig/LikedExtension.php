<?php

namespace AppBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;


class LikedExtension extends \Twig_Extension
{
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('liked', [$this, 'likedFilter'])
        ];
    }

    public function likedFilter($user, $publication)
    {
        $likeRepo = $this->doctrine->getRepository('BackendBundle:Like');
        $publicationLiked = $likeRepo->findOneBy([
            'user' => $user,
            'publication' => $publication
        ]);

        if (!empty($publicationLiked) && is_object($publicationLiked)) {
            return true;
        }
        return false;
    }

    public function getName()
    {
        return 'liked_extension';
    }
}