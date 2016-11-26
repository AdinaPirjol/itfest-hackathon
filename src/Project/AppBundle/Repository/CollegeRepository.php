<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 12:10 PM
 */

namespace Project\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CollegeRepository extends EntityRepository
{
    public function getColleges($data)
    {
        return $this->createQueryBuilder('c')
            ->select('c.name')
            ->where('c.name like :college')
            ->setParameter('college', '%' . $data['college'] . '%')
            ->getQuery()->getResult();
    }
}