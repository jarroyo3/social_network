<?php

namespace AppBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;


class FollowingExtension extends \Twig_Extension
{
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('following', [$this, 'followingFilter'])
        ];
    }

    public function followingFilter($user, $followed)
    {
        $followingRepo = $this->doctrine->getRepository('BackendBundle:Following');
        $userFollowing = $followingRepo->findOneBy([
            'user' => $user,
            'followed' =>$followed
        ]);

        $result = false;
        
        if (!empty($userFollowing) && is_object($userFollowing)) {
            $result = true;
        }

        return $result;
    }

    public function getName()
    {
        return 'following_extension';
    }
}