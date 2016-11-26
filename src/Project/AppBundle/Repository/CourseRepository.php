<?php

namespace Project\AppBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Project\AppBundle\Entity\Project;

class CourseRepository extends EntityRepository
{
    const ID = 'AppBundle:Course';

    /**
     * @param $params
     * @return \Doctrine\ORM\Query
     */
    public function getFilterCoursesData($params)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.id, c.name, p.id as professorId')
            ->addSelect(
                [
                    $qb->expr()->concat(
                        $qb->expr()->concat(
                            'p.firstName',
                            $qb->expr()->literal(' ')
                        ), 'p.lastName')
                    . 'as professor'
                ]
            )
            ->join('c.courseProfessors', 'cp')
            ->join('cp.professor', 'p');

        if (!empty($params['professor'])) {
            $qb->andWhere('p.id in (:professor)')->setParameter('professor', $params['professor']);
        }

        if (!empty($params['course'])) {
            $qb->andWhere('c.id in (:course)')->setParameter('course', $params['course']);
        }

        return $qb->getQuery();
    }
}