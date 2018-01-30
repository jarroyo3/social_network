<?php

namespace BackendBundle\Repository;

class UserRepository extends \Doctrine\ORM\EntityRepository 
{
    public function getFollowingUsers($user)
    {
        $em = $this->getEntityManager();
        $followingRepo = $em->getRepository('BackendBundle:Following');
        $following = $followingRepo->findBy([
            'user' => $user
        ]);

        $followingArray = [];
        foreach ($following as $follow) {
            $followingArray[] = $follow->getFollowed();
        }

        $userRepo = $em->getRepository('BackendBundle:User');
        $users = $userRepo->createQueryBuilder('u')
            ->where('u.id != :user AND u.id IN (:following)')
            ->setParameter('user', $user->getId())
            ->setParameter('following', $followingArray)
            ->orderBy('u.id', 'DESC');
        
        return $users;
    }
}