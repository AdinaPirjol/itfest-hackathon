<?php
/**
 * Created by PhpStorm.
 * User: nnao9_000
 * Date: 11/26/2016
 * Time: 11:49 AM
 */

namespace Project\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CourseProfessorsRepository extends EntityRepository
{

    public function findCourses($params)
    {
        $qb = $this->createQueryBuilder('cp');
        $qb->select('c.id, c.name, p.id as professorId')
            ->join('cp.course', 'c')
            ->join('cp.professor', 'p')
            ->join('c.college', 'cl');

        if (!empty($params['professor'])) {
            $qb->andWhere('p.id in (:professor)')->setParameter('professor', $params['professor']);
        }

        if (!empty($params['course'])) {
            $qb->andWhere('c.id in (:course)')->setParameter('course', $params['course']);
        }

        if (!empty($params['courseInput'])) {
            $qb->andWhere('c.name like :courseInput')->setParameter('courseInput', '%' . $params['courseInput'] . '%');
        }

        if (!empty($params['college'])) {
            $qb->andWhere('cl.id = :college')->setParameter('college', $params['college']);
        }

        return $qb->getQuery();
    }
}