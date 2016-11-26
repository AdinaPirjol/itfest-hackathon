<?php
/**
 * Created by PhpStorm.
 * User: laurentiu.codreanu
 * Date: 04-07-16
 * Time: 22:31
 */

namespace Project\AppBundle\Services;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Project\AppBundle\Entity\Course;
use Project\UserBundle\Entity\StudentGroup;
use Project\UserBundle\Entity\StudentStream;
use Symfony\Component\Translation\Translator;

class GroupService
{

    const ID = 'app.group';
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var Translator
     */
    protected $translator;
    /**
     * @return Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param Registry $doctrine
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }
    /**
     * @param Course $course
     * @param $formdata
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editGroup(StudentGroup $group, $formdata)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();

        try {
            /** @var StudentStream $studentGroup */
            $studentStream = $em->getRepository('UserBundle:StudentStream')->findOneBy(array('streamName'=>$formdata['stream']));

            $group->setGroupName($formdata['groupname']);
            $group->setSpecialisation($formdata['specialisation']);
            $group->setStream($studentStream);

            $em->persist($group);
            $em->flush();
            $em->getConnection()->commit();
        } catch(\Exception $e) {
            $em->getConnection()->rollBack();
            return $e->getMessage();
        }

        return null;
    }
}