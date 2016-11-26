<?php

namespace Project\AppBundle\Controller;

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

        $form = $this->createForm(
            $this->get(\Project\AppBundle\Form\Type\CreateCourseType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'course' => $course,
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
        $result= $courseService->getColleges($request->request->get('college'));
        return new JsonResponse($result);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_ADMIN")
     */
    public function editCourseAction(Request $request, $id)
    {
        /** @var CourseService $projectService */
        $courseService = $this->get(CourseService::ID);
        /** @var Course $course */
        $course = $this->getDoctrine()->getRepository('AppBundle:Course')->find($id);

        if (UtilService::isNullObject($course)) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(
            $this->get(\Project\AppBundle\Form\Type\CreateCourseType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'course' => $course,
            ]
        );

        $error = false;

        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                $error = $courseService->editCourse($course, $formData);
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
}