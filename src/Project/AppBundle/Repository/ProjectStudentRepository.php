<?php

namespace Project\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Project\AppBundle\Entity\Project;
use Project\AppBundle\Entity\ProjectStudent;

class ProjectStudentRepository extends EntityRepository
{
    const ID = 'AppBundle:ProjectStudent';

    /**
     * @param ProjectStudent $projectStudent
     * @return mixed
     */
    public function invalidateOtherApplicationsForStudent(ProjectStudent $projectStudent)
    {
        return $this->createQueryBuilder('ps')
            ->update()
            ->set('ps.status', ':status')->setParameter('status', ProjectStudent::STATUS_INVALIDATED)
            ->where('ps.student = :student')->setParameter('student', $projectStudent->getStudent()->getId())
            ->andWhere('ps.status = :statusPending')->setParameter('statusPending', ProjectStudent::STATUS_PENDING)
            ->andWhere('ps.project <> :project')->setParameter('project', $projectStudent->getProject()->getId())
            ->getQuery()
            ->execute();
    }

    /**
     * @return array
     */
    public function getReportChartStatistics()
    {
        return $this->createQueryBuilder('ps')
            ->select('ps.status, count(ps) as total')
            ->join('ps.project', 'p')
            ->join('ps.student', 's')
            ->join('s.userCredentials', 'uc')
            ->where('p.status = :status')->set('status', Project::STATUS_ACTIVE)
            ->where('uc.credentialsExpired  <> 1')
            ->groupBy('ps.status')
            ->getQuery()
            ->getResult();
    }
}