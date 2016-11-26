<?php

namespace Project\AppBundle\Controller;

use Project\AppBundle\Entity\CourseProfessors;
use Project\AppBundle\Entity\Event;
use Project\UserBundle\Entity\User;
use Project\UserBundle\Entity\UserCredentials;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\UserBundle\Model\Group;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Project\AppBundle\Entity\College;
use Project\AppBundle\Form\Type\ReportType;
use Project\AppBundle\Entity\Course;
use Project\AppBundle\Form\Type\CreateGroupType;
use Project\AppBundle\Services\CourseService;
use Project\AppBundle\Services\DefaultService;
use Project\AppBundle\Services\GroupService;
use Project\AppBundle\Services\ProjectService;
use Project\AppBundle\Services\UtilService;
use Project\UserBundle\Entity\StudentGroup;
use Project\UserBundle\Entity\UserType;
use Project\UserBundle\Form\Type\CreateUserType;
use Project\UserBundle\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function indexAction(Request $request)
    {
        return $this->render(
            'AppBundle:Admin:index.html.twig'
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function createUsersAction(Request $request)
    {
        /** @var DefaultService $defaultService */
        $defaultService = $this->get(DefaultService::ID);

        $form = $this->createForm(
            $this->get(CreateUserType::ID),
            array(),
            array(
                'translator' => $this->get('translator'),
                'filterData' => $defaultService->getUserCreateFormFilterData()
            )
        );

        $error = null;
        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();

                /** @var UserService $userService */
                $userService = $this->get(UserService::ID);
                $response = $userService->createUser($formData);
                $error = $response['error'] ? $response['message'] : null;
            }
        }

        $params['form'] = $form->createView();
        $params['error'] = $error;

        return $this->render(
            'AppBundle:Admin:createUsers.html.twig',
            $params
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function importUsersAction(Request $request)
    {
        $errors = null;

        if ($request->getMethod() == 'POST') {
            $role = null;
            $uploadFile = null;
            if (!is_null($request->get('student'))) {
                $role = UserType::ROLE_USER;
                /** @var UploadedFile $uploadFile */
                $uploadFile = $request->files->get('submitFileStudent');
            } elseif (!is_null($request->get('professor'))) {
                $role = UserType::ROLE_ADMIN;
                /** @var UploadedFile $uploadFile */
                $uploadFile = $request->files->get('submitFileProfessor');
            }

            if (!is_null($role) && !is_null($uploadFile)) {
                /** @var DefaultService $defaultService */
                $defaultService = $this->get(DefaultService::ID);

                if ($uploadFile instanceof UploadedFile) {
                    $errors = $defaultService->uploadFile($uploadFile, $role);
                }
            } else {
                // todo
            }
        }

        return $this->render(
            'AppBundle:Admin:importUsers.html.twig',
            [
                'errors' => $errors
            ]
        );
    }

    /**
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\Response|void
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function createReportsAction(Request $request)
    {
        /** @var ProjectService $projectService */
        $projectService = $this->get(ProjectService::ID);

        $form = $this->createForm(
            $this->get(ReportType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'filterData' => $projectService->getReportFormData()
            ]
        );

        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                return $projectService->createReport($formData['report']);
            }
        }

        return $this->render(
            'AppBundle:Admin:createReport.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function reportChartAction(Request $request)
    {
        /** @var ProjectService $projectService */
        $projectService = $this->get(ProjectService::ID);
        $statistics = $projectService->getReportChartStatistics();

        return new JsonResponse($statistics);
    }

    public function createCourseAction(Request $request)
    {
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);
        /** @var CourseService $courseService */
        $courseService = $this->get(CourseService::ID);

        /** @var Course $group */
        $course = new Course();
        /** @var User[] $users */
        $users = $this->getDoctrine()->getRepository('UserBundle:User')->findAll();
        $courseProf = [];
        foreach($users as $user) {
            $courseProf[$user->getId()] = $user->getFirstName() . ' ' . $user->getLastName();
        }

        $form = $this->createForm(
            $this->get(\Project\AppBundle\Form\Type\CreateCourseType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'course' => $course,
                'courseProf' => $courseProf
            ]
        );

        $error = null;
        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                $error = $courseService->editCourse($course, $formData,$userService->getCurrentUser());
            }
        }

        return $this->render(
            'AppBundle:Course:courseAdd.html.twig',
            [
                'form' => $form->createView(),
                'course' => $course,
                'error' => $error
            ]
        );
    }

    /**
     * @Route("/get-colleges", name="get-colleges"  )
     * @return JsonResponse
     */
    public function getCollegesAction(Request $request) {
        /** @var CourseService $courseService */
        $courseService = $this->get(CourseService::ID);
        $results= $courseService->getColleges($request->request->get('college'));
        $final = [];
        foreach ($results as $result) {
            $final[] = $result['name'];
        }

        return new JsonResponse($final);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_ADMIN")
     */
    public function editCourseAction(Request $request, $id)
    {
        /** @var CourseService $courseService */
        $courseService = $this->get(CourseService::ID);
        /** @var UserService $userService */
        $userService= $this->get(UserService::ID);
        /** @var Course $course */
        $course = $this->getDoctrine()->getRepository('AppBundle:Course')->find($id);

        if (UtilService::isNullObject($course)) {
            throw new NotFoundHttpException();
        }

        /** @var User[] $users */
        $users = $this->getDoctrine()->getRepository('UserBundle:User')->findAll();
        $courseProf = [];
        foreach($users as $user) {
            $courseProf[$user->getId()] = $user->getFirstName() . ' ' . $user->getLastName();
        }

        $form = $this->createForm(
            $this->get(\Project\AppBundle\Form\Type\CreateCourseType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'course' => $course,
                'courseProf' => $courseProf
            ]
        );

        $error = false;

        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                $error = $courseService->editCourse($course, $formData, $userService->getCurrentUser());
            }
        }

        return $this->render(
            'AppBundle:Course:courseEdit.html.twig',
            [
                'form' => $form->createView(),
                'course' => $course,
                'error' => $error
            ]
        );
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @Secure(roles="ROLE_ADMIN")
     */
    public function projectDeleteAction(Request $request, $id)
    {
        /** @var Course $course */
        $course = $this->getDoctrine()->getRepository('AppBundle:Course')->find($id);

        if (UtilService::isNullObject($course)) {
            return new JsonResponse(
                [
                    'error' => true,
                    'message' => $this->get('translator')->trans('error_not_found')
                ]
            );
        }

        return new JsonResponse(
            [
                'error' => false
            ]
        );
    }

    public function createGroupAction(Request $request)
    {
        /** @var GroupService $groupService */
        $groupService = $this->get(GroupService::ID);

        /** @var StudentGroup $group */
        $group = new StudentGroup();

        $form = $this->createForm(
            $this->get(CreateGroupType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'group' => $group,
            ]
        );

        $error = null;
        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                $error = $groupService->editGroup($group, $formData);
            }
        }

        return $this->render(
            'AppBundle:Group:groupAdd.html.twig',
            [
                'form' => $form->createView(),
                'group' => $group,
                'error' => $error
            ]
        );
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_ADMIN")
     */
    public function editGroupAction(Request $request, $id)
    {
        /** @var GroupService $groupService */
        $groupService = $this->get(GroupService::ID);
        /** @var StudentGroup $group */
        $group = $this->getDoctrine()->getRepository('UserBundle:StudentGroup')->find($id);

        if (UtilService::isNullObject($group)) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(
            $this->get(CreateGroupType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'group' => $group,
            ]
        );

        $error = false;

        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                $error = $groupService->editGroup($group, $formData);
            }
        }

        return $this->render(
            'AppBundle:Group:groupEdit.html.twig',
            [
                'form' => $form->createView(),
                'group' => $group,
                'error' => $error
            ]
        );
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @Secure(roles="ROLE_ADMIN")
     */
    public function groupDeleteAction(Request $request, $id)
    {
        /** @var StudentGroup $studentGroup */
        $studentGroup = $this->getDoctrine()->getRepository('AppBundle:Course')->find($id);

        if (UtilService::isNullObject($studentGroup)) {
            return new JsonResponse(
                [
                    'error' => true,
                    'message' => $this->get('translator')->trans('error_not_found')
                ]
            );
        }

        return new JsonResponse(
            [
                'error' => false
            ]
        );
    }


    /**
     * @Route("/list-events", name="list_events"  )
     */
    public function listEventsAction(Request $request)
    {
        return $this->render(
            'AppBundle:Calendar:listCalendar.html.twig',
        ['id'=>$request->get('id')]);
    }

    public function getEventAjaxAction($id)
    {
        $json = [];
        /** @var array $events */
        $events = $this->getDoctrine()->getRepository('AppBundle:Event')->findBy(['course'=>$id]);
        /** @var Event $event */
        foreach ($events as $event) {
            $jsonT['start'] = date_format($event->getStartDate(), 'Y-m-d h:i:s');
            $jsonT['title'] = $event->getCourse()->getName();
            if (!empty($event->getEndDate())) {
                $jsonT['end'] = date_format($event->getEndDate(), 'Y-m-d h:i:s');
            }
            $jsonT['url'] = '/ro/courses/view-event/' . $event->getCourse()->getId();
            $json[] = $jsonT;
        }

        return new JsonResponse($json);
    }

    public function listModeratorsAction($id)
    {
        /** @var CourseProfessors $courseProf */
        $courseProf = $this->getDoctrine()->getRepository('AppBundle:CourseProfessors')->findBy(['course'=>$id]);

        $profList = [];
        foreach ($courseProf as $prof) {
            $profList[] = $prof->getProfessor();
        }

        return $this->render(
            'AppBundle:Course:listModerators.html.twig',
            ['prof' => $profList, 'course'=>$id]);
    }

    public function addModeratorAction(Request $request)
    {
        $em = $this->getDoctrine();
        $moderator = $em->getRepository('UserBundle:UserCredentials')->findOneBy(['emailCanonical'=>$request->request->get('exampleInputEmail3')]);

        if ($moderator instanceof UserCredentials) {
            /** @var CourseProfessors $courseProf */
            $courseProf = $em->getRepository('AppBundle:CourseProfessors')->findOneBy(['course'=>$request->request->get('courseID')]);
            if ($courseProf instanceof CourseProfessors) {
                $c = new CourseProfessors();
                $c->setCourse($courseProf->getCourse());
                $c->setProfessor($em->getRepository('UserBundle:User')->findOneBy(
                    ['userCredentials'=> $moderator->getId()]
                    )
                );
                $em->getManager()->persist($c);
                $em->getManager()->flush();
            }
        }

        return $this->redirect($this->generateUrl('list_moderators', ['id' => $request->request->get('courseID')]));
    }

    public function unsubscribeModeratorAction($id)
    {
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);
        /** @var User $user */
        $user = $userService->getCurrentUser();
        if ($user instanceof  User) {
            $moderators = $this->getDoctrine()->getRepository('AppBundle:CourseProfessors')->findBy(['course' => $id]);
            $courseProfessor = $this->getDoctrine()->getRepository('AppBundle:CourseProfessors')->findOneBy(['course' => $id, 'professor' => $user->getId()]);
            if ($courseProfessor instanceof CourseProfessors && count($moderators) > 1) {
                $this->getDoctrine()->getManager()->remove($courseProfessor);
                $this->getDoctrine()->getManager()->flush();
            }

        }

        return $this->redirect($this->generateUrl('filter-projects'));

    }

}