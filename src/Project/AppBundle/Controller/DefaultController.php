<?php

namespace Project\AppBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Project\AppBundle\Services\DefaultService;
use Project\AppBundle\Services\MailService;
use Project\AppBundle\Services\UtilService;
use Project\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var AuthorizationChecker $securityContext */
        $securityContext = $this->container->get('security.authorization_checker');

        /** @var DefaultService $defaultService */
        $defaultService = $this->get(DefaultService::ID);

        $params = $defaultService->loginAction($request);

        if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->render(
                'AppBundle:Security:login.html.twig',
                $params
            );
        }

        return $this->render(
            'AppBundle::homepage.html.twig',
            [
                'params' => null
            ]
        );
    }

    /**
     * @return Response
     */
    public function resetPasswordAction()
    {
        $securityContext = $this->container->get('security.authorization_checker');

        if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->render('AppBundle:Security:recoverPassword.html.twig');
        }
    }
}
