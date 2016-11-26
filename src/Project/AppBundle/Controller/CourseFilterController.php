<?php

namespace Project\AppBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Knp\Component\Pager\Paginator;
use Project\AppBundle\Entity\Project;
use Project\AppBundle\Entity\ProjectStudent;
use Project\AppBundle\Form\Type\EditProjectType;
use Project\AppBundle\Form\Type\ProjectFilterListType;
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

class ProjectFilterController extends Controller
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
        $courseService = $this->get(ProjectService::ID);
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);

        $projectFilterListType = new ProjectFilterListType();

        $form = $this->createForm(
            $projectFilterListType,
            array(),
            array(
                'translator' => $translator,
                'filterData' => $courseService->getCourseFilterFormData()
            )
        );

        $courses = $courseService->getCourseFilterData(array());

        $pagination = $paginator->paginate(
            $courses, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10 /*limit per page*/
        );

        //$params['projects'] = $projects;
        $params['form'] = $form->createView();
        $params['pagination'] = $pagination;

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
        /** @var ProjectService $projectService */
        $projectService = $this->get(ProjectService::ID);
        /** @var UserService $userService */
        $userService = $this->get(UserService::ID);

        try {
            $projectFilterListType = new ProjectFilterListType();
            $params = $request->get($projectFilterListType->getName());
            $projects = $projectService->getProjectFilterData($params);
            $canApply = $userService->canApplyForProject();

            $pagination = $paginator->paginate(
                $projects, /* query NOT result */
                $request->query->getInt('page', 1)/*page number*/,
                10 /*limit per page*/
            );

            $data = $this->renderView(
                'AppBundle:Project:projectList.html.twig',
                [
                    'pagination' => $pagination,
                    'canApply' => $canApply
                ]
            );

            $response = array(
                'error' => false,
                'projects' => $data
            );

        } catch (\Exception $e) {
            $response = array(
                'error' => true,
                //'message' => $e->getMessage(), /** for debugging */
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
        /** @var Project $project */
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);

        /** @var UserService $userService */
        $userService = $this->container->get('app.user');
        /** @var User $user */
        $currentUser = $userService->getCurrentUser();

        $canApply = ($currentUser->getUserType()->getRoleType() == UserType::ROLE_USER);
        $canEdit = ($this->isGranted(UserType::ROLE_ADMIN) || $this->isGranted(UserType::ROLE_SUPER_ADMIN))
            && ($project->getProfessor()->getId() == $currentUser->getId());

        return $this->render(
            'AppBundle:Project:projectMoreInfo.html.twig',
            [
                'project' => $project,
                'canApply' => $canApply,
                'canEdit' => $canEdit
            ]
        );
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @Secure(roles="ROLE_USER")
     */
    public function applyProjectAction(Request $request, $id)
    {
        /** @var Translator $translator */
        $translator = $this->get('translator');

        $response = [
            'error' => false,
            'message' => $translator->trans('apply_project.message.succes')
        ];

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
            $response = [
                'error' => true,
                'message' => $translator->trans('apply_project.message.not_allowed')
            ];

            return new JsonResponse($response);
        }

        /** @var UserService $userService */
        $userService = $this->container->get('app.user');
        /** @var User $user */
        $user = $userService->getCurrentUser();
        /** @var Project $project */
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);

        if (UtilService::isNullObject($project)) {
            $response = [
                'error' => true,
                'message' => $translator->trans('apply_project.message.not_exist', ['%id%' => $id])
            ];

            return new JsonResponse($response);
        }

        /** @var ProjectStudent $projectStudent */
        $projectStudent = $this->getDoctrine()->getRepository('AppBundle:ProjectStudent')->findOneBy(
            [
                'student' => $user->getId(),
                'project' => $project->getId()
            ]
        );

        if (!UtilService::isNullObject($projectStudent)) {
            switch ($projectStudent->getStatus()) {
                case ProjectStudent::STATUS_PENDING:
                    $message = $translator->trans('apply_project.message.already_applied');
                    break;
                case ProjectStudent::STATUS_REJECTED:
                    $message = $translator->trans('apply_project.message.rejected');
                    break;
                case ProjectStudent::STATUS_ACCEPTED:
                    $message = $translator->trans('apply_project.message.already_accepted');
                    break;
                case ProjectStudent::STATUS_INVALIDATED:
                    $message = $translator->trans('apply_project.message.invalidated');
                    break;
                default:
                    $message = '';
                    break;
            }

            $response = [
                'error' => true,
                'message' => $message
            ];

            return new JsonResponse($response);
        }

        $projectStudent = new ProjectStudent();
        $projectStudent
            ->setStudent($user)
            ->setProject($project);

        $this->getDoctrine()->getManager()->persist($projectStudent);
        $this->getDoctrine()->getManager()->flush();

        /** @var MailService $mailService */
        $mailService = $this->get(MailService::ID);
        $mailService->sendMail2(
            $user,
            MailService::TYPE_SEND_APPLICATION,
            [
                'project' => $project,
                'student' => $user
            ]
        );

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
        /** @var Project $project */
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);

        if (UtilService::isNullObject($project)) {
            throw new NotFoundHttpException();
        }

        if ($project->getProfessor()->getId() != $user->getId()) {
            throw new AccessDeniedHttpException();
        }

        if ($project->getStatus() == Project::STATUS_INACTIVE) {
            /** @todo */
        }

        $form = $this->createForm(
            $this->get(EditProjectType::ID),
            [],
            [
                'translator' => $this->get('translator'),
                'project' => $project,
                'filterData' => $projectService->getEditProjectFormData()
            ]
        );

        $error = false;

        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                $formData = $form->getData();
                $error = $projectService->editProject($project, $user, $formData);
            }
        }

        return $this->render(
            'AppBundle:Project:projectEdit.html.twig',
            [
                'form' => $form->createView(),
                'project' => $project,
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
}