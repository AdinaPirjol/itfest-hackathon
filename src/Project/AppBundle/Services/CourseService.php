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
use Project\AppBundle\Entity\College;
use Project\AppBundle\Entity\Course;
use Project\AppBundle\Entity\CourseProfessors;
use Project\AppBundle\Repository\CollegeRepository;
use Project\AppBundle\Repository\CourseProfessorsRepository;
use Project\AppBundle\Repository\CourseRepository;
use Project\UserBundle\Entity\StudentGroup;
use Project\UserBundle\Entity\User;
use Project\UserBundle\Entity\UserType;
use Project\UserBundle\Repository\UserRepository;
use Symfony\Component\Translation\Translator;

class CourseService
{

    const ID = 'app.course';
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
    public function editCourse(Course $course, $formdata, User $user)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();

        try {
            $course->setName($formdata['name']);
            /** @var College $college */
            $college = $em->getRepository('AppBundle:College')->findOneBy(['name' => $formdata['college']]);
            $course->setCollege($college);

            $em->persist($course);

            $courseProfessor = new CourseProfessors();
            $courseProfessor->setCourse($course);
            $courseProfessor->setProfessor($user);
            $em->persist($courseProfessor);

            $em->flush();
            $em->getConnection()->commit();
        } catch(\Exception $e) {
            $em->getConnection()->rollBack();
            return $e->getMessage();
        }

        return null;
    }

    /**
     * @param $params
     * @return array
     */
    public function getCourseFilterData($params)
    {
        /** @var CourseProfessorsRepository $em */
        $em = $this->getEntityManager()->getRepository("AppBundle:CourseProfessors");
        return $em->findCourses($params);
    }

    public function getCourseFilterFormData()
    {
        $filterData = [
            'professor' => [],
            'course' => []
        ];

        $em = $this->getEntityManager();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(UserRepository::ID);
        /** @var CourseRepository$courseRepository */
        $courseRepository = $em->getRepository(CourseRepository::ID);

        /** @var User[] $professors */
        $professors = $userRepository->findByRole(UserType::ROLE_ADMIN);
        /** @var College[] $colleges */
        $colleges = $em->getRepository('AppBundle:College')->findAll();

        foreach ($professors as $professor) {
            $filterData['professor'][$professor->getId()] = $professor->getName();
        }

        /** @var Course $course */
        foreach($courseRepository->findAll() as $course) {
            $filterData['course'][$course->getId()] = $course->getName();
        }

        foreach ($colleges as $college) {
            $filterData['college'][$college->getId()] = $college->getName();
        }


        return $filterData;
    }

    public function getColleges($data)
    {
        /** @var CollegeRepository $er */
        $er = $this->getDoctrine()->getRepository('AppBundle:College');
        return array_values($er->getColleges($data));
    }
}