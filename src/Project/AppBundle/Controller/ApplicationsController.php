<?php

namespace Project\AppBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Project\AppBundle\Entity\Project;
use Project\AppBundle\Entity\ProjectStudent;
use Project\AppBundle\Form\Type\ApplicationSubmitType;
use Project\AppBundle\Services\ApplicationService;
use Project\AppBundle\Services\DefaultService;
use Project\AppBundle\Services\ProjectService;
use Project\AppBundle\Services\UtilService;
use Project\UserBundle\Entity\StudentGroup;
use Project\UserBundle\Entity\User;
use Project\UserBundle\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Translator;

class ApplicationsController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction(Request $request)
    {
        $params = array();

        /** @var UserService $userService */
        $userService = $this->container->get('app.user');
        /** @var User $user */
        $user = $userService->getCurrentUser();

        /** @var ApplicationSubmitType $applicationsSubmitType */
        $applicationsSubmitType = $this->get('applications.type');

        /** @var Translator $translator */
        $translator = $this->get('translator');

        $filterData = array();
        $group = $user->getGroup();
        $filterData['group'] = '';
        $filterData['specialisation'] = '';
        $groupName = '';

        if ($group instanceof StudentGroup) {
            $filterData['group'] = $user->getGroup()->getId();
            $filterData['specialisation'] = $user->getGroup()->getSpecialisation();
            $groupName = $group->getGroupName();
        }

        $form = $this->createForm(
            $applicationsSubmitType,
            array(),
            array(
                'translator' => $translator,
                'filterData' => $filterData
            )
        );

        $filename = sprintf('application-%s.pdf', $user->getLastName() . '-' . $user->getFirstName() . '-' .
            $groupName);
        $path = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "download" .
            DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($path)) {
            $params['created'] = 0;
        } else {
            $params['created'] = $filename;
        }

        $params['form'] = $form->createView();

        return $this->render(
            'AppBundle:Applications:applicationSubmit.html.twig',
            $params
        );
    }

    /**
     * @param Request $request
     * @param int $id
     * @param bool $answer
     * @param int $studentId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @Secure(roles="ROLE_ADMIN")
     */
    public function acceptDeclineAction(Request $request, $id, $answer, $studentId)
    {
        /** @var Translator $translator */
        $translator = $this->get('translator');
        /** @var ProjectService $projectService */
        $projectService = $this->get(ProjectService::ID);

        /** @var Project $project */
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);

        if (UtilService::isNullObject($project)) {
            return $this->render(
                'AppBundle::homepage.html.twig',
                ['params' => ['acceptProject' => $translator->trans('apply_project.message.prof_answer.invalid.project')]]
            );
        }

        /** @var User $student */
        $student = $this->getDoctrine()->getRepository('UserBundle:User')->find($studentId);

        if (UtilService::isNullObject($student)) {
            return $this->render(
                'AppBundle::homepage.html.twig',
                ['params' => ['acceptProject' => $translator->trans('apply_project.message.prof_answer.invalid.student')]]
            );
        }

        /** @var ProjectStudent $projectStudent */
        $projectStudent = $this->getDoctrine()->getRepository('AppBundle:ProjectStudent')
            ->findOneBy(
                [
                    'student' => $student->getId(),
                    'project' => $project->getId()
                ]
            );

        if (UtilService::isNullObject($projectStudent)) {
            return $this->render(
                'AppBundle::homepage.html.twig',
                ['params' => ['acceptProject' => $translator->trans('apply_project.message.prof_answer.invalid.project_student')]]
            );
        }

        $message = $projectService->updateProject($projectStudent, $student, $answer);

        return $this->render(
            'AppBundle::homepage.html.twig',
            ['params' => ['acceptProject' => $message]]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function generatePdfAjaxAction(Request $request)
    {
        if ($request->getMethod() != 'POST') {
            $response = array(
                'error' => true,
                'message' => 'Only POST HTTP request method is supported for this operation!'
            );
            return new JsonResponse(
                $response
            );
        } else {
            $params = array();

            /** @var UserService $userService */
            $userService = $this->container->get('app.user');
            /** @var User $user */
            $user = $userService->getCurrentUser();
            $error = null;
            if ($user instanceof User) {
                /** @var ApplicationSubmitType $applicationSubmitType */
                $applicationSubmitType = $this->get('applications.type');

                /** @var Translator $translator */
                $translator = $this->get('translator');

                $filterData = array();
                $filterData['group'] = $user->getGroup()->getId();
                $filterData['specialisation'] = $user->getGroup()->getSpecialisation();

                $form = $this->createForm(
                    $applicationSubmitType,
                    array(),
                    array(
                        'translator' => $translator,
                        'filterData' => $filterData
                    )
                );

                $form->submit($request);
                if ($form->isValid()) {
                    /** @var ApplicationService $params */
                    $params = $this->get('applications.submit')->getParameters($user, $request->get($applicationSubmitType->getName()));

                    if (!is_null($user->getGroup())) {
                        $params['department'] = $user->getGroup()->getSpecialisation();
                    }
                    $error = $params['error'] ? $params['message'] : null;
                } else {
                    //todo
                }
            }

            $filename = sprintf('application-%s.pdf', $user->getLastName() . '-' . $user->getFirstName() . '-' . $user->getGroup()->getGroupName());
            if (!file_exists($this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "download" .
                DIRECTORY_SEPARATOR . $filename)
            ) {
                $this->get('knp_snappy.pdf')->generateFromHtml(
                    $this->renderView(
                        'AppBundle:Applications:application.html.twig',
                        $params
                    ),
                    '..' . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . $filename
                );
            }

            $response = array(
                'error' => $error,
                'filename' => $filename
            );

            return $this->redirect($this->generateUrl('applications-pdf-index'));
        }
    }

    /**
     * @param $filename
     * @return Response
     */
    public function downloadApplicationFormAction($filename)
    {
        $path = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "download" .
            DIRECTORY_SEPARATOR . $filename;
        $content = file_get_contents($path);

        $response = new Response();

        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename);

        $response->setContent($content);
        return $response;
    }
}
