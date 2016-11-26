<?php

namespace Project\AppBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Knp\Component\Pager\Paginator;
use Project\AppBundle\Entity\Course;
use Project\AppBundle\Entity\CourseProfessors;
use Project\AppBundle\Entity\CourseSubscribers;
use Project\AppBundle\Entity\Project;
use Project\AppBundle\Entity\ProjectStudent;
use Project\AppBundle\Form\Type\CourseFilterListType;
use Project\AppBundle\Form\Type\EditProjectType;
use Project\AppBundle\Form\Type\ProjectFilterListType;
use Project\AppBundle\Repository\CourseSubscribersRepository;
use Project\AppBundle\Repository\ProjectRepository;
use Project\AppBundle\Services\CourseService;
use Project\AppBundle\Services\MailService;
use Project\AppBundle\Services\ProjectService;
use Project\AppBundle\Services\UtilService;
use Project\UserBundle\Entity\User;
use Project\UserBundle\Entity\UserType;
use Project\UserBundle\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseFilterController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction(Request $request)
    {
        $params = array();

        /** @var Translator $translator */
        $translator = $this->get('translator');
        /** @var Paginator $paginator */
        $paginator  = $this->get('knp_paginator');
        /** @var CourseService $courseService */
        $courseService = $this->get(CourseService::ID);
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);

        $user = $userService->getCurrentUser();

        $courseFilterListType = new CourseFilterListType();

        $form = $this->createForm(
            $courseFilterListType,
            array(),
            array(
                'translator' => $translator,
                'filterData' => $courseService->getCourseFilterFormData()
            )
        );

        $courseProf = $courseService->getCourseFilterData(array());

        $enabled = [];
        /** @var CourseSubscribersRepository $courseSubRepo */
        $courseSubRepo = $this->getDoctrine()->getRepository('AppBundle:CourseSubscribers');
        foreach($courseProf->getResult() as $c) {
            $cs = $courseSubRepo->findOneBy(['student' => $user->getId(), 'course' => $c['id']]);
            if(!UtilService::isNullObject($cs)) {
                $enabled[$c['id']] = true;
            } else {
                $enabled[$c['id']] = false;
            }
        }

        $params['enabled'] = $enabled;

        $pagination = $paginator->paginate(
            $courseProf, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10 /*limit per page*/
        );

        //$params['projects'] = $projects;
        $params['form'] = $form->createView();
        $params['pagination'] = $pagination;
        $params['canEdit'] = $userService->getCurrentUser();

        return $this->render(
            'AppBundle:Course:courseListFormFilters.html.twig',
            $params
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_USER")
     */
    public function listAjaxAction(Request $request)
    {
        /** @var Paginator $paginator */
        $paginator  = $this->get('knp_paginator');
        /** @var CourseService $courseService */
        $courseService = $this->get(CourseService::ID);
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);
        $user = $userService->getCurrentUser();

        try {
            $projectFilterListType = new CourseFilterListType();
            $params = $request->get($projectFilterListType->getName());

            $projects = $courseService->getCourseFilterData($params);

            $enabled = [];
            /** @var CourseSubscribersRepository $courseSubRepo */
            $courseSubRepo = $this->getDoctrine()->getRepository('AppBundle:CourseSubscribers');
            foreach($projects->getResult() as $c) {
                $cs = $courseSubRepo->findOneBy(['student' => $user->getId(), 'course' => $c['id']]);
                if(!UtilService::isNullObject($cs)) {
                    $enabled[$c['id']] = true;
                } else {
                    $enabled[$c['id']] = false;
                }
            }

            $pagination = $paginator->paginate(
                $projects, /* query NOT result */
                $request->query->getInt('page', 1)/*page number*/,
                10 /*limit per page*/
            );

            $data = $this->renderView(
                'AppBundle:Course:courseList.html.twig',
                [
                    'pagination' => $pagination,
                    'enabled' => $enabled
                ]
            );

            $response = array(
                'error' => false,
                'projects' => $data
            );

        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                'message' => $e->getMessage(), /** for debugging */
            );
        }

        return new JsonResponse(
            $response
        );
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Secure(roles="ROLE_USER")
     */
    public function viewProjectAction(Request $request, $id)
    {
        /** @var Course $course */
        $course = $this->getDoctrine()->getRepository('AppBundle:Course')->find($id);

        /** @var UserService $userService */
        $userService = $this->container->get('app.user');
        /** @var User $user */
        $currentUser = $userService->getCurrentUser();

        $canView = $this->getDoctrine()->getRepository('AppBundle:CourseSubscribers')->findOneBy(['course'=>$id, 'student' => $currentUser->getId()]);
        $canEdit = $this->getDoctrine()->getRepository('AppBundle:CourseProfessors')->findOneBy(['course'=>$id, 'professor' => $currentUser->getId()]);

        $courseProf = $this->getDoctrine()->getRepository('AppBundle:CourseProfessors')->findBy(['course'=>$id, 'professor' => $currentUser->getId()]);

        return $this->render(
            'AppBundle:Project:projectMoreInfo.html.twig',
            [
                'course' => $course,
                'courseProf' => $courseProf,
                'canView' => $canView,
                'canEdit' => $canEdit
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @Secure(roles="ROLE_USER")
     */
    public function applyProjectAction(Request $request)
    {
        $id = $request->request->get('id');
        $enabled = $request->request->get('enable');

        /** @var Translator $translator */
        $translator = $this->get('translator');

        $response = [
            'error' => false,
            'message' => $translator->trans('apply_project.message.succes')
        ];

        /** @var UserService $userService */
        $userService = $this->container->get('app.user');
        /** @var User $user */
        $user = $userService->getCurrentUser();

        /** @var Course $course */
        $course = $this->getDoctrine()->getRepository('AppBundle:Course')->find($id);

        if (UtilService::isNullObject($course)) {
            $response = [
                'error' => true,
                'message' => $translator->trans('apply_project.message.not_exist', ['%id%' => $id])
            ];

            return new JsonResponse($response);
        }

        if ($enabled == 1){
            $courseSub = new CourseSubscribers();
            $courseSub->setStudent($user);
            $courseSub->setCourse($course);
            $this->getDoctrine()->getManager()->persist($courseSub);
        } else {
            /** @var CourseSubscribersRepository $courseSubRepo */
            $courseSubRepo = $this->getDoctrine()->getRepository('AppBundle:CourseSubscribers');
            $courseSub = $courseSubRepo->findOneBy(['student' => $user->getId(), 'course' => $id]);
            if (!UtilService::isNullObject($courseSub)) {
                $this->getDoctrine()->getManager()->remove($courseSub);
            }
        }
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse($response);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_ADMIN")
     */
    public function addProjectAction(Request $request)
    {
        /** @var ProjectService $projectService */
        $projectService = $this->get(ProjectService::ID);
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);

        $project = new Project();

        $form = $this->createForm(
            $this->get(EditProjectType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'project' => $project,
                'filterData' => $projectService->getEditProjectFormData()
            ]
        );

        $error = null;
        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                $error = $projectService->editProject($project, $userService->getCurrentUser(), $formData);
            }
        }

        return $this->render(
            'AppBundle:Project:projectAdd.html.twig',
            [
                'form' => $form->createView(),
                'project' => $project,
                'error' => $error
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Secure(roles="ROLE_ADMIN")
     */
    public function listProjectsByProfessorAction(Request $request)
    {
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);
        $user = $userService->getCurrentUser();

        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Project');
        /** @var Project[] $projects */
        $projects = $projectRepository->getProjectsByProfessor($user);

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $projects, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10 /*limit per page*/
        );

        return $this->render(
            'AppBundle:Project:projectListByProfessor.html.twig',
            array(
                'projects' => $pagination
            )
        );
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_ADMIN")
     */
    public function projectEditAction(Request $request, $id)
    {
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);
        $user = $userService->getCurrentUser();

        /** @var ProjectService $projectService */
        $projectService = $this->get(ProjectService::ID);
        /** @var Course $course */
        $course = $this->getDoctrine()->getRepository('AppBundle:Course')->find($id);
        /** @var CourseProfessors $courseProf */
        $courseProf = $this->getDoctrine()->getRepository('AppBundle:Course')->findOneBy(['professor' => $user->getId()]);

        if (UtilService::isNullObject($course)) {
            throw new NotFoundHttpException();
        }

        if (UtilService::isNullObject($courseProf) || $courseProf->getProfessor()->getId() != $user->getId()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(
            $this->get(EditProjectType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'course' => $course,
                'filterData' => [
                    'courseProf' => $courseProf = $this->getDoctrine()->getRepository('AppBundle:Course')->findBy(['professor' => $user->getId()])
                ]
            ]
        );

        $error = false;

        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                $error = $projectService->editProject($course, $user, $formData);
            }
        }

        return $this->render(
            'AppBundle:Project:projectEdit.html.twig',
            [
                'form' => $form->createView(),
                'project' => $course,
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
        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Project');

        /** @var Project $project */
        $project = $projectRepository->find($id);

        if (UtilService::isNullObject($project)) {
            return new JsonResponse(
                [
                    'error' => true,
                    'message' => $this->get('translator')->trans('apply_project.message.invalid.project')
                ]
            );
        }

        if ($project->getRemainingStudentNo() < $project->getStudentNo()) {
            return new JsonResponse(
                [
                    'error' => true,
                    'message' => $this->get('translator')->trans('apply_project.message.invalid.project')
                ]
            );
        }

        $projectRepository->deleteProject($id);

        return new JsonResponse(
            [
                'error' => false
            ]
        );
    }

    /**
     * @return JsonResponse
     * @Secure(roles="ROLE_USER")
     */
    public function recentProjectsAjaxAction()
    {
        /** @var ProjectRepository $projectRepository */
        $projectRepository = $this->getDoctrine()->getRepository('AppBundle:Project');
        $projects = $projectRepository->getRecentProjects();

        $data = $this->renderView(
            'AppBundle:Dashboard:recentProjects.html.twig',
            [
                'projects' => $projects
            ]
        );

        return new JsonResponse($data);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewEventAction(Request $request, $id)
    {
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);
        $user = $userService->getCurrentUser();

        /** @var Course $course */
        $course = $this->getDoctrine()->getRepository('AppBundle:Course')->find($id);
        $events = $this->getDoctrine()->getRepository('AppBundle:Event')->findBy(['course' => $course->getId()]);

        /** @var array $courseProfessors */
        $courseProfessors = $this->getDoctrine()->getRepository('AppBundle:CourseProfessors')->findBy(['course' => $course->getId()]);
        /** @var CourseProfessors $courseProfessor */
        foreach ($courseProfessors as $courseProfessor) {
            if ($courseProfessor->getProfessor()->getId() == $user->getId()) {
                return $this->render(
                    'AppBundle:Event:eventEdit.html.twig',
                    [
                        'canEdit' => true,
                        'course' => $course,
                        'events' => $events
                    ]
                );
            }
        }

        return $this->render(
            'AppBundle:Event:eventEdit.html.twig',
            [
                'canEdit' => false,
                'course' => $course,
                'events' => $events
            ]
        );
    }

    public function addEventAction($id)
    {
        return $this->render(
            'AppBundle:Event:addEvent.html.twig',
            ['courseId' => $id]
        );
    }

}