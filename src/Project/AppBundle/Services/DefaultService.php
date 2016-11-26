<?php

namespace Project\AppBundle\Services;

use JMS\JobQueueBundle\Entity\Job;
use Project\AppBundle\Entity\Project;
use Project\AppBundle\Entity\Tag;
use Project\AppBundle\Entity\TagProject;
use Project\UserBundle\Entity\StudentStream;
use Project\UserBundle\Entity\User;
use Project\UserBundle\Entity\UserCredentials;
use Project\UserBundle\Entity\UserType;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Project\UserBundle\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class DefaultService
{
    const ID = 'app.default';

    /**
     * @var Container
     */
    private $container;

    private $tokenManager;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function setTokenManager($tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @return Registry
     */
    public function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    public function getName()
    {
        return 'adina';
    }

    public function loginAction(Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        $token = $this->tokenManager
            ->generateCsrfToken('authenticate');

        return array(
            'token' => $token,
            'last_username' => $lastUsername,
            'error' => $error
        );
    }

    public function insert()
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user instanceof User) {
            $userType = new UserType();
            $userType->setRoleType(UserType::ROLE_ADMIN);
            $this->getDoctrine()->getManager()->persist($userType);

            $professor = new User();
            $professor->setDescription('nu');
            $professor->setUserType($userType);
            //$professor->setUser($user);
            $em->persist($professor);
            $em->flush();

            $project = new Project();
            $project->setDescription('palma');
            $project->setName('mata');
            $project->setProfessor($professor);
            $em->persist($project);
            $em->flush();

            $tag = new Tag();
            $tag->setName('virtual reality');
            $em->persist($tag);
            $em->flush();

            $projectTag = new TagProject();
            $projectTag->setProject($project);
            $projectTag->setTag($tag);
            $em->persist($projectTag);
            $em->flush();
        }
    }

    public function addProject($user, $params)
    {
        try {
            /** var Project $project */
            $project = new Project();

            $project->setProfessor($user);
            $project->setDescription($params['description']);
            $project->setName($params['name']);
            $project->setNameRo($params['nameRo']);
            $project->setStudentNo($params['noStudents']);
            $project->setRemainingStudentNo($params['noStudents']);
            /** @var StudentStream $stream */
            $stream = $this->getDoctrine()->getRepository('UserBundle:StudentStream')->find($params['stream']);
            $project->setStream($stream);
            $project->setCreated(new \DateTime());
            $project->setModified(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();
        } catch (\Exception $e) {
            return;
        }
    }

    public function uploadFile(UploadedFile $uploadFile, $role)
    {
        /** @todo move somewhere else */
        $uploadPath = '../upload/';

        $fileName = $uploadFile->getClientOriginalName();

        $uploadFile->move($uploadPath, $fileName);
        $inputFileType = pathinfo($uploadPath . $fileName, PATHINFO_EXTENSION);

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($uploadPath . $fileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($uploadPath . $fileName);

        } catch (Exception $e) {
            throw new \Exception('Error loading file "' . pathinfo($fileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = "D";
        $data = [];

        //  Loop through each row of the worksheet in turn
        for ($row = 1; $row <= $highestRow; $row++) {
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray(
                'A' . $row . ':' . $highestColumn . $row,
                null,
                true,
                false
            );
            $data[] = $rowData;
        }

        /** @var UserService $userService */
        $userService = $this->container->get('app.user');

        $errors = [];
        foreach ($data as $row) {
            $cols = reset($row);

            if ($cols[0] == 'username' || is_null($cols[0])) {
                continue;
            }

            $params = array(
                'username' => $cols[0],
                'firstname' => $cols[1],
                'lastname' => $cols[2],
                'email' => $cols[3],
                'role' => $role
            );

            $response = $userService->createUser($params);

            if ($response['error'] === true) {
                $errors[] = $response['message'];
            }
        }

        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove(array($uploadPath . $fileName));

        return $errors;
    }

    /**
     * @param UploadedFile $uploadFile
     * @param $role
     * @throws \Exception
     * @throws \Throwable
     */
    public function uploadFile2(UploadedFile $uploadFile, $role)
    {
        /** @todo move somewhere else */
        $uploadPath = $this->container->getParameter('import_directory');
        $inputFileType = pathinfo($uploadPath . $uploadFile->getClientOriginalName(), PATHINFO_EXTENSION);

        if ($inputFileType != 'csv') {
            throw new \Exception();
        }

        $fileName = 'import.csv';

        $uploadFile->move($uploadPath, $fileName);

        // todo command
        /*
        $job = new Job('users:bulk', array('some-args', [$fileName, $role]));
        $em = $this->container->get('doctrine')->getManager();
        $em->persist($job);
        $em->flush($job);
        */

        // todo command
        $root_dir = $this->container->get('kernel')->getRootDir();
        $command = sprintf('php %s/console users:bulk %s %s', $root_dir, $fileName, $role);
        $process = new Process($command);
        $process->run();
    }

    public function getLocales()
    {
        /** @var Translator $translator */
        $translator = $this->container->get('translator');

        return array(
            'ro' => $translator->trans('lang.ro'),
            'en' => $translator->trans('lang.en'),
            'fr' => $translator->trans('lang.fr'),
            'de' => $translator->trans('lang.de')
        );
    }

    public function getRoles()
    {
        /** @var Registry $doctrine */
        $doctrine = $this->container->get('doctrine');
        /** @var Translator $translator */
        $translator = $this->container->get('translator');

        /** @var UserType[] $userTypes */
        $userTypes = $doctrine->getManager()->getRepository('UserBundle:UserType')->findAll();
        $roles = array();

        foreach ($userTypes as $userType) {
            $roles[$userType->getId()] = $translator->trans('roles.' . $userType->getRoleType());
        }

        return $roles;
    }

    public function getUserCreateFormFilterData()
    {
        return array(
            'locales' => $this->getLocales(),
            'roles' => $this->getRoles()
        );
    }

    public function updateUserToken($email, $token, $date)
    {
        /** @var UserCredentials $user */
        $user = $this->getDoctrine()
            ->getRepository('UserBundle:UserCredentials')
            ->findOneBy(
                array('email' => $email)
            );
        $user->setConfirmationToken($token);
        $user->setPasswordRequestedAt($date);

        $em = $this->getDoctrine()->getManager();
        $em->merge($user);
        $em->flush();
    }
}