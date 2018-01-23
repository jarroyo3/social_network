<?php

namespace AppBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;


class UserStatsExtension extends \Twig_Extension
{
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('user_stats', [$this, 'userStatsFilter'])
        ];
    }

    public function userStatsFilter($user)
    {
        $followingRepo = $this->doctrine->getRepository('BackendBundle:Following');
        $publicationRepo = $this->doctrine->getRepository('BackendBundle:Publication');
        $likeRepo = $this->doctrine->getRepository('BackendBundle:Like');
        
        $userFollowing = $followingRepo->findBy([
            'user' => $user
        ]);

        $userFollowers = $followingRepo->findBy([
            'followed' => $user
        ]);

        $userPublication = $publicationRepo->findBy([
            'user' => $user
        ]);

        $userLikes = $likeRepo->findBy([
            'user' => $user
        ]);

        $result = [
            'following' => count($userFollowing),
            'followers' => count($userFollowers),
            'publications' => count($userPublication),
            'likes' => count($userLikes)
        ];

        return $result;
    }

    public function getName()
    {
        return 'user_stats_extension';
    }
}