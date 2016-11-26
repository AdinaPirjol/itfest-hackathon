<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 11:19 AM
 */

namespace Project\AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class CommentRepository extends EntityRepository
{
    public function sortComments($id) {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.event', 'e')
            ->where('e.id = :id')->setParameter('id', $id)
            ->orderBy('c.rating', 'desc')
            ->getQuery()->getResult();
    }
}