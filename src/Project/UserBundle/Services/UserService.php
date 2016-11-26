<?php

namespace Project\UserBundle\Services;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Doctrine\UserManager;
use Project\AppBundle\Entity\ProjectStudent;
use Project\AppBundle\Services\MailService;
use Project\AppBundle\Services\UtilService;
use Project\UserBundle\Entity\StudentGroup;
use Project\UserBundle\Entity\User;
use Project\UserBundle\Entity\UserCredentials;
use Project\UserBundle\Entity\UserType;
use Project\UserBundle\Repository\UserTypeRepository;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Translation\Translator;

class UserService
{
    const ID = 'app.user';

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorageInterface;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @param $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function setTokenStorageInterface($tokenStorageInterface)
    {
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    public function setUserManager($userManager)
    {
        $this->userManager = $userManager;
    }

    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setMailService($mailService)
    {
        $this->mailService = $mailService;
    }

    public function createUser($params)
    {
        $response = [
            'error' => false,
            'message' => ''
        ];

        $em = $this->entityManager;
        /** @var UserTypeRepository $userTypeRepository */
        $userTypeRepository = $em->getRepository(UserTypeRepository::ID);

        $role = $params['role'];
        if (!$role instanceof UserType) {
            if (is_numeric($role)) {
                $userType = $userTypeRepository->find($role);
            } else {
                /** @var UserType $userType */
                $userType = $userTypeRepository->findOneBy(['roleType' => $role]);
            }
        } else {
            $userType = $role;
        }

        $em->getConnection()->beginTransaction();

        try {
            /** create random password */
            $password = base64_encode(random_bytes(6));

            /** @var UserCredentials $userCredentials */
            $userCredentials = $this->userManager->createUser();
            $userCredentials->setUsername($params['username'])
                ->setEmail($params['email'])
                ->setPlainPassword($password)
                ->setRoles([$userType->getRoleType()])
                ->setEnabled(true);

            $now = new \DateTime();
            if ($now->format('m') < 10) {  // if user created before October, credentials should expire in October
                $userCredentials->setExpiresAt(new \DateTime($now->format('Y') . '-10-01'));
            } else { // else, they should expire next year in October
                $userCredentials->setExpiresAt(new \DateTime((int)$now->format('Y')+1 . '-10-01'));
            }

            $this->userManager->updateUser($userCredentials, false);

            $user = new User();
            $user->setUserCredentials($userCredentials)
                ->setFirstName($params['firstname'])
                ->setLastName($params['lastname'])
                ->setUserType($userType);

            $em->persist($user);
            $em->flush();

            $this->sendCreateUserAccountMail($user, $password);

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();

            $response = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    public function sendCreateUserAccountMail(User $user, $password)
    {
        $this->mailService->sendMail2(
            $user,
            MailService::TYPE_CREATE_ACCOUNT,
            [
                'username' => $user->getUserCredentials()->getUsername(),
                'password' => $password
            ]
        );
    }

    public function getCurrentUser()
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository('UserBundle:User')
            ->findOneBy(
                array(
                    'userCredentials' => $this->tokenStorageInterface->getToken()->getUser()->getId()
                )
            );

        if (UtilService::isNullObject($user)) {
            throw new \Exception('', 403);
        }

        return $user;
    }

    public function updateUserProfile(UserCredentials $userCredential, $params)
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository('UserBundle:User')
            ->findOneBy(
                array(
                    'userCredentials' => $userCredential
                )
            );

        $user->setFirstName($params['firstname']);
        $user->setLastName($params['lastname']);
        $user->setDescription($params['description']);
        $user->setPhoneNumber($params['phone']);
        $user->setPreferredLocale($params['preferred_locale']);

        if (!empty($params['group'])) {
            /** @var StudentGroup $group */
            $group = $this->entityManager
                ->getRepository('UserBundle:StudentGroup')
                ->find($params['group']);
            $user->setGroup($group);
        }

        $userCredential->setEmail($params['email']);

        $this->userManager->updateUser($userCredential);

        $em = $this->entityManager;

        $em->merge($user);
        $em->flush();
    }

    /**
     * @param $userId
     * @return null|User
     */
    public function getUserById($userId)
    {
        return $this->entityManager->getRepository('UserBundle:User')
            ->findOneBy(
                array(
                    'userCredentials' => $userId
                )
            );
    }

    /**
     * @return StudentGroup[]
     */
    public function getGroups()
    {
        return $this->entityManager
            ->getRepository('UserBundle:StudentGroup')
            ->findAll();
    }

    /**
     * @return array
     */
    public function getGroupsForm()
    {
        $groups = $this->getGroups();
        $groupsForm = array();
        foreach ($groups as $group) {
            $groupsForm[$group->getId()] = $group->getGroupName();
        }

        return $groupsForm;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserDetails($user)
    {
        return array(
            'userType' => $user->getUserType()->getRoleType(),
            'username' => $user->getUserCredentials()->getUsername(),
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'email' => $user->getUserCredentials()->getEmail(),
            'group' => !is_null($user->getGroup()) ? $user->getGroup()->getGroupName() : "",
            'description' => $user->getDescription(),
            'phone' => $user->getPhoneNumber()
        );
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function canApplyForProject()
    {
        $canApply = false;
        $user = $this->getCurrentUser();
        /** @var ProjectStudent $project */
        $project = $this->entityManager->getRepository('AppBundle:ProjectStudent')
            ->findOneBy(
                array(
                    'student' => $user->getId(),
                    'status' => ProjectStudent::STATUS_ACCEPTED
                )
            );

        if ($user->getUserType()->getRoleType() == UserType::ROLE_USER
            && UtilService::isNullObject($project)) {
            $canApply = true;
        }

        return $canApply;
    }
}