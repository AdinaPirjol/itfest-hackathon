<?php

namespace Project\UserBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Project\AppBundle\Services\DefaultService;
use Project\AppBundle\Services\MailService;
use Project\AppBundle\Services\UtilService;
use Project\UserBundle\Entity\User;
use Project\UserBundle\Entity\UserCredentials;
use Project\UserBundle\Form\Type\ChangeUserType;
use Project\UserBundle\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    public function viewProfileAction(Request $request, $id)
    {
        /** @var UserService $userService */
        $userService = $this->container->get('app.user');

        $currentUser = $userService->getCurrentUser();
        $edit = false;

        if ($id == null) {
            $user = $currentUser;
            $edit = true;
        } else {
            $user = $userService->getUserById($id);
            if (!UtilService::isNullObject($user)
                && $user->getId() == $currentUser->getId()
            ) {

                $edit = true;
            }
        }

        /** @var User $user */
        if (UtilService::isNullObject($user)) {
            $params = array(
                'error' => true
            );
        } else {
            $params = $userService->getUserDetails($user);
            $params['error'] = false;
            $params['edit'] = $edit;
        }

        return $this->render(
            'UserBundle:Profile:viewProfile.html.twig',
            array(
                'user' => $user,
                'params' => $params
            )
        );
    }

    public function editProfileAction(Request $request)
    {
        /** @var UserService $userService */
        $userService = $this->container->get(UserService::ID);
        /** @var DefaultService $defaultService */
        $defaultService = $this->container->get(DefaultService::ID);
        /** @var User $user */
        $user = $userService->getCurrentUser();

        $formProfile = $this->createForm(
            $this->get(ChangeUserType::ID),
            array(),
            array(
                'translator' => $this->get('translator'),
                'user' => $user,
                'groups' => $userService->getGroupsForm(),
                'locales' => $defaultService->getLocales()
            )
        );

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.change_password.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user->getUserCredentials());

        return $this->render(
            'UserBundle:Profile:editProfile.html.twig',
            [
                'formProfile' => $formProfile->createView(),
                'form' => $form->createView(),
                'user' => $user
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateProfileAjaxAction(Request $request)
    {
        $response = array(
            'error' => false,
            'message' => ''
        );

        /** @var UserService $userService */
        $userService = $this->container->get(UserService::ID);
        /** @var DefaultService $defaultService */
        $defaultService = $this->container->get(DefaultService::ID);
        /** @var User $user */
        $user = $userService->getCurrentUser();

        $form = $this->createForm(
            $this->get(ChangeUserType::ID),
            array(),
            array(
                'translator' => $this->get('translator'),
                'user' => $user,
                'groups' => $userService->getGroupsForm(),
                'locales' => $defaultService->getLocales()
            )
        );

        if ($request->isMethod('POST') && $request->get($form->getName())) {
            $form->submit($request);

            if ($form->isValid()) {
                try {
                    $formData = $form->getData();
                    $userService->updateUserProfile(
                        $user->getUserCredentials(),
                        $formData
                    );
                } catch (\Exception $e) {
                    $response['error'] = true;
                    $response['message'] = $e->getMessage();
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => (string) $form->getErrors(true)
                ];
            }
        } else {
            $response['error'] = true;
        }

        return new JsonResponse(
            $response
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function changePasswordAjaxAction(Request $request)
    {
        $response = array(
            'error' => false,
            'message' => ''
        );

        /** @var UserService $userService */
        $userService = $this->container->get('app.user');
        /** @var User $user */
        $user = $userService->getCurrentUser();

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');
        $event = new GetResponseUserEvent($this->getUser(), $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.change_password.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user->getUserCredentials());

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
                $userManager = $this->get('fos_user.user_manager');

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);

                $userManager->updateUser($this->getUser());

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_profile_show');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(
                    FOSUserEvents::CHANGE_PASSWORD_COMPLETED,
                    new FilterUserResponseEvent($this->getUser(), $request, $response)
                );
            } catch(\Exception $e) {
                $response['error'] = true;
            }
        } else {
            $message = array();
            foreach ($form->getErrors() as $error) {
                $message[] = $error->getMessage();
            }

            $response = array(
                'error' => true,
                'message' => $message
            );
        }

        return new JsonResponse(
            $response
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function recoverPasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var DefaultService $defaultService */
        $defaultService = $this->get('app.default');
        /** @var MailService $mailService */
        $mailService = $this->get(MailService::ID);

        $userName = $request->get('username');

        if (is_null($userName)) {
            return $this->render('AppBundle:Security:login.html.twig');
        }

        /** @var UserCredentials $userCredentials */
        $userCredentials = $em->getRepository('UserBundle:UserCredentials')
            ->findOneBy(['username' => $userName]);

        if (!$userCredentials instanceof UserCredentials) {
            return $this->render('AAppBundle:Security:login.html.twig');
        }

        $token = sha1(time() . $userCredentials->getId());
        $date = new \DateTime(date('Y-m-d H:i:s'));

        $defaultService->updateUserToken($userCredentials->getEmail(), $token, $date);

        $params['confirmationToken'] = $token;
        $params['userId'] = $userCredentials->getId();

        /** @var User $user */
        $user = $em->getRepository('UserBundle:User')
            ->findOneBy(['user_credentials_id' => $userCredentials->getId()]);


        if (!UtilService::isNullObject($user)) {
            $mailService->sendMail2(
                $user,
                MailService::TYPE_RECOVER_PASSWORD,
                $params
            );
        }

        return $this->redirect($this->generateUrl('index'));
    }

    public function confirmForgotPasswordAction($userId, $answer, $token)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository('UserBundle:UserCredentials')
            ->findOneBy(['email' => $userId]);

        if (!$user instanceof User) {
            return $this->render('AppBundle:Security:login.html.twig');
        }

        if ($answer == 0) {
            return $this->render('AppBundle:Security:login.html.twig');
        }

        if ($token == '') {
            return $this->render('AppBundle:Security:login.html.twig');
        }

        /** @var UserCredentials $userCredentials */
        $userCredentials = $user->getUserCredentials();

        $userCredentials->setPlainPassword($token);

        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('fos_user.user_manager');
        $userManager->updatePassword($userCredentials);

        return $this->render('AppBundle:Security:login.html.twig');
    }
}
