<?php

namespace Project\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Project\AppBundle\Entity\ProjectStudent;
use Project\UserBundle\Entity\UserType;

class UserRepository extends EntityRepository
{
    const ID = 'UserBundle:User';

    public function createGoodReport()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('g.groupName as groupName, p.name as projectName')
            ->addSelect(
                [
                    $qb->expr()->concat(
                        $qb->expr()->concat(
                            'prof.firstName',
                            $qb->expr()->literal(' ')
                        ), 'prof.lastName')
                    . 'as professorName'
                ]
            )
            ->addSelect(
                [
                    $qb->expr()->concat(
                        $qb->expr()->concat(
                            's.firstName',
                            $qb->expr()->literal(' ')
                        ), 's.lastName')
                    . 'as studentName'
                ]
            )
            ->join('AppBundle:ProjectStudent', 'ps', Expr\Join::WITH, 'ps.student = u.id')
            ->join('ps.project', 'p')
            ->join('p.professor', 'prof')
            ->join('ps.student', 's')
            ->where('ps.status = :status')->setParameter('status', ProjectStudent::STATUS_ACCEPTED)
            ->leftJoin('s.group', 'g');

        return $qb->getQuery()->getResult();
    }

    public function createBadReport()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('concat(u.firstName, u.lastName) as studentName, g.groupName as groupName, u.phoneNumber as phoneNumber, uc.email as email')
            ->leftJoin('AppBundle:ProjectStudent', 'ps', Expr\Join::WITH, 'ps.student = u.id')
            ->leftJoin('u.group', 'g')
            ->join('u.userCredentials', 'uc')
            ->where(
                $qb->expr()->orX(
                    'ps.id is null',
                    'ps.status <> :status'
                )
            )
            ->setParameter('status', ProjectStudent::STATUS_ACCEPTED)
            ->andWhere('u.userType = :userType')->setParameter('userType', UserType::ROLE_USER_ID);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $role
     * @return array
     */
    public function findByRole($role)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->join('u.userCredentials', 'uc')
            ->where('uc.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%');

        return $qb->getQuery()->getResult();
    }
}