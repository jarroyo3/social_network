<?php

namespace AppBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;


class GetUserExtension extends \Twig_Extension
{
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('get_user', [$this, 'getUserFilter'])
        ];
    }

    public function getUserFilter($userId)
    {
        $userRepo = $this->doctrine->getRepository('BackendBundle:User');
        $user = $userRepo->findOneBy([
            'id' => $userId,
        ]);

        if (!empty($user) && is_object($user)) {
            return $user;
        }
        return false;
    }

    public function getName()
    {
        return 'get_user_extension';
    }
}