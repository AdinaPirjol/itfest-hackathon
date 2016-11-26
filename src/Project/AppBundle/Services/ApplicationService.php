<?php

namespace Project\AppBundle\Services;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Project\AppBundle\Entity\ProjectStudent;
use Project\UserBundle\Entity\User;
use Symfony\Component\Translation\Translator;

class ApplicationService
{
    const ID = 'app.application';

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var Translator
     */
    protected $translator;

    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getDoctrine()
    {
        return $this->doctrine;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function getParameters(User $user, $data)
    {
        $params = [
            'error' => false,
            'message' => ''
        ];

        try {
            $params['firstname'] = $user->getFirstName();
            $params['lastname'] = strtoupper($user->getLastName());

            /** @var ProjectStudent $projectStudent */
            $projectStudent = $this->getDoctrine()->getRepository('AppBundle:ProjectStudent')->findOneBy(array('student' => $user->getId()));

            if ($projectStudent instanceof ProjectStudent) {
                $this->translator->setLocale($user->getGroup()->getStream()->getStreamName());

                $params['title'] = $projectStudent->getProject()->getName();
                $params['proffirstname'] = $projectStudent->getProject()->getProfessor()->getFirstName();
                $params['proflastname'] = strtoupper($projectStudent->getProject()->getProfessor()->getLastName());
                $params['proftitle'] = $data['proftitle'];
                $params['designdata'] = $data['design'];
                $params['contribution'] = $data['contribution'];
                $params['graphics'] = $data['graphics'];
                $params['knowledge'] = implode(", ", $data['knowledge']);
                $params['environment'] = $data['environment'];
                $params['serve'] = $data['serve'];
                $params['date'] = $data['preparation'];
            }
        } catch (\Exception $e) {
            $params = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        return $params;
    }
}