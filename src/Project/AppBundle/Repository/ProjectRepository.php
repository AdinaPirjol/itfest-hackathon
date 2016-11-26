<?php

namespace Project\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Project\AppBundle\Entity\Project;
use Project\UserBundle\Entity\User;

class ProjectRepository extends EntityRepository
{
    const ID = 'AppBundle:Project';

    /**
     * @return array
     */
    public function getRecentProjects()
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->orderBy('p.dateCreated', 'desc')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $params
     * @return \Doctrine\ORM\Query
     */
    public function getFilterProjectsData($params)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p.id, p.name, p.nameRo, p.description, u.id as professorId, p.remainingStudentNo')
            ->addSelect(
                [
                    $qb->expr()->concat(
                        $qb->expr()->concat(
                            'u.firstName',
                            $qb->expr()->literal(' ')
                        ), 'u.lastName')
                    . 'as professor'
                ]
            )
            ->join('p.professor', 'u')
            ->andWhere('p.status = :status')->setParameter('status', Project::STATUS_ACTIVE);

        if (!empty($params['availability'])) {
            if ($params['availability'] == Project::STATUS_ACTIVE) {
                $qb->andWhere('p.remainingStudentNo <> 0');
            } else {
                $qb->andWhere('p.remainingStudentNo = 0');
            }
        }

        if (!empty($params['stream'])) {
            $qb->andWhere('p.stream = :stream')->setParameter('stream', $params['stream']);
        }

        if (!empty($params['professor'])) {
            $qb->andWhere('p.professor in (:professor)')->setParameter('professor', $params['professor']);
        }

        if (!empty($params['tag'])) {
            $qb->join('AppBundle:TagProject', 'tp', Expr\Join::WITH, 'tp.project = p.id')
                ->andWhere('tp.tag in (:tag)')->setParameter('tag', $params['tag']);
        }

        return $qb->getQuery();
    }

    /**
     * @param User $user
     * @return \Doctrine\ORM\Query
     */
    public function getProjectsByProfessor(User $user)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.status = :status')->setParameter('status', Project::STATUS_ACTIVE)
            ->andWhere('p.professor = :professor')->setParameter('professor', $user->getId())
            ->getQuery();
    }

    /**
     * @param int $project
     * @return mixed
     */
    public function deleteProject($project)
    {
        return $this->createQueryBuilder('p')
            ->delete()
            ->where('p.id = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->execute();
    }
}