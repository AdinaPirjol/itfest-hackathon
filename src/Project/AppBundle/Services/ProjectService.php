<?php

namespace Project\AppBundle\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Liuggio\ExcelBundle\Factory;
use Project\AppBundle\Entity\Course;
use Project\AppBundle\Entity\Project;
use Project\AppBundle\Entity\ProjectStudent;
use Project\AppBundle\Entity\Tag;
use Project\AppBundle\Entity\TagProject;
use Project\AppBundle\Repository\CourseRepository;
use Project\AppBundle\Repository\ProjectRepository;
use Project\AppBundle\Repository\ProjectStudentRepository;
use Project\AppBundle\Repository\TagRepository;
use Project\UserBundle\Entity\StudentStream;
use Project\UserBundle\Entity\User;
use Project\UserBundle\Entity\UserType;
use Project\UserBundle\Repository\StudentStreamRepository;
use Project\UserBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectService
{
    const ID = 'app.project';

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var \Liuggio\ExcelBundle\Factory
     */
    protected $phpExcel;

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

    public function setPhpExcel($phpExcel)
    {
        $this->phpExcel = $phpExcel;
    }

    public function getPhpExcel()
    {
        return $this->phpExcel;
    }

    public function setMailService($mailService)
    {
        $this->mailService = $mailService;
    }

    public function getMailService()
    {
        return $this->mailService;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }



    /**
     * @param string $reportType
     * @return array|void
     */
    public function createReport($reportType)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getEntityManager()->getRepository(UserRepository::ID);

        /** @var \PHPExcel $phpExcelObject */
        $phpExcelObject = new \PHPExcel();

        $phpExcelObject
            ->getProperties()->setCreator('FILS UPB Team')
            ->setLastModifiedBy('FILS UPB Team');

        switch ($reportType) {
            case ProjectStudent::REPORT_YES_STUDENTS:
                $items = $userRepository->createGoodReport();

                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Name')
                    ->setCellValue('B1', 'Group')
                    ->setCellValue('C1', 'Project')
                    ->setCellValue('D1', 'Coordinating Professor')
                    ->fromArray($items, null, 'A2');
                break;
            case ProjectStudent::REPORT_NO_STUDENTS:
                $items = $userRepository->createBadReport();
                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Name')
                    ->setCellValue('B1', 'Group')
                    ->setCellValue('C1', 'Phone Number')
                    ->setCellValue('D1', 'Email')
                    ->fromArray($items, null, 'A2');
                break;
            default:
                //not impl
                break;
        }

        $phpExcelObject->getActiveSheet()->setTitle('FILS Diploma Project Report');

        // the one to open
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        // $writer = $this->getPhpExcel()->createWriter($phpExcelObject, 'Excel5');

        $writer = call_user_func(array('\PHPExcel_IOFactory', 'createWriter'), $phpExcelObject, 'Excel5');

        // create the response
        // $response = $this->getPhpExcel()->createStreamedResponse($writer);

        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            array()
        );

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'report.xls'
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param ProjectStudent $projectStudent
     * @param User $user
     * @param bool $answer
     * @return string
     */
    public function updateProject(ProjectStudent $projectStudent, User $user, $answer)
    {
        $translator = $this->getTranslator();
        $mailService = $this->getMailService();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if ($projectStudent->getStatus() != ProjectStudent::STATUS_PENDING) {
            switch ($projectStudent->getStatus()) {
                case ProjectStudent::STATUS_INVALIDATED:
                    return $translator->trans('apply_project.message.prof_response.invalidated');
                    break;
                case ProjectStudent::STATUS_ACCEPTED:
                    return $translator->trans('apply_project.message.prof_response.accepted');
                    break;
                case ProjectStudent::STATUS_REJECTED:
                    return $translator->trans('apply_project.message.prof_response.rejected');
                    break;
            }
        }

        if ((bool)$answer == false) {
            $mailService->sendMail2(
                $user,
                MailService::TYPE_REJECT_APPLICATION,
                [
                    'project' => $projectStudent->getProject(),
                    'optional' => false
                ]
            );

            $projectStudent->setStatus(ProjectStudent::STATUS_REJECTED);
            $em->persist($projectStudent);
            $em->flush();

            return $translator->trans('apply_project.message.prof_response.success');
        }

        $project = $projectStudent->getProject();

        if ($project->getRemainingStudentNo() == 0) {
            $mailService->sendMail2(
                $user,
                MailService::TYPE_REJECT_APPLICATION,
                [
                    'project' => $projectStudent->getProject(),
                    'optional' => true
                ]
            );

            $projectStudent->setStatus(ProjectStudent::STATUS_REJECTED);
            $em->persist($projectStudent);
            $em->flush();

            return $translator->trans('apply_project.message.prof_response.no_spots_available');
        }

        $em->getConnection()->beginTransaction();

        try {
            $project->decrementRemainingStudentNo();
            $em->persist($project);

            if ($project->getRemainingStudentNo() == 0) {
                $this->updatePendingProjectApplicationsWithNoAvailableSpots($project);
            }

            $this->invalidateOtherApplicationsForStudent($projectStudent);

            $projectStudent->setStatus(ProjectStudent::STATUS_ACCEPTED);
            $em->persist($projectStudent);

            $mailService->sendMail2(
                $user,
                MailService::TYPE_ACCEPT_APPLICATION,
                [
                    'project' => $project
                ]
            );

            $em->flush();
            $em->getConnection()->commit();
        } catch(\Exception $e) {
            $em->getConnection()->rollBack();
            return $translator->trans('error_request');
        }

        return $translator->trans('apply_project.message.prof_response.success');
    }

    /**
     * @param Project $project
     */
    public function updatePendingProjectApplicationsWithNoAvailableSpots(Project $project)
    {
        /** @var ProjectStudent[] $pendingApplications */
        $pendingApplications = $this->getDoctrine()->getManager()->getRepository('AppBundle:ProjectStudent')
            ->findBy(
                [
                    'project' => $project->getId(),
                    'status' => ProjectStudent::STATUS_PENDING
                ]
            );

        foreach ($pendingApplications as $pendingApplication) {
            $this->getMailService()->sendMail2(
                $pendingApplication->getStudent(),
                MailService::TYPE_REJECT_APPLICATION,
                [
                    'project' => $project,
                    'optional' => true
                ]
            );

            $pendingApplication->setStatus(ProjectStudent::STATUS_REJECTED);
            $this->getDoctrine()->getManager()->persist($pendingApplication);
            $this->getDoctrine()->getManager()->flush();
        }
    }

    /**
     * @param ProjectStudent $projectStudent
     */
    public function invalidateOtherApplicationsForStudent(ProjectStudent $projectStudent)
    {
        /** @var ProjectStudentRepository $projectStudentRepository */
        $projectStudentRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:ProjectStudent');
        $projectStudentRepository->invalidateOtherApplicationsForStudent($projectStudent);
    }

    public function getStreamsFormData()
    {
        /** @var StudentStream[] $streams */
        $streams = $this->getEntityManager()->getRepository(StudentStreamRepository::ID)->findAll();

        $filterData = array();
        foreach ($streams as $stream) {
            $filterData[$stream->getId()] = $this->getTranslator()->trans('streams.' . $stream->getStreamName());
        }

        return $filterData;
    }

    public function getTagsFormData()
    {
        /** @var Tag[] $tags */
        $tags = $this->getEntityManager()->getRepository(TagRepository::ID)->findAll();

        $filterData = array();
        foreach ($tags as $tag) {
            $filterData[$tag->getId()] = $tag->getName();
        }

        return $filterData;
    }

    public function getEditProjectFormData()
    {
        return [
            'tags' => $this->getTagsFormData(),
            'streams' => $this->getStreamsFormData(),
        ];
    }

    /**
     * @param Project $project
     * @param User $user
     * @param $formdata
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editProject(Project $project, User $user, $formdata)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();

        try {
            /** @var StudentStream $stream */
            $stream = $em->getRepository('UserBundle:StudentStream')->find($formdata['stream']);

            if (!$project->getRemainingStudentNo()) {
                $project->setRemainingStudentNo($formdata['studentNo']);
            }

            $project
                ->setName($formdata['name'])
                ->setNameRo($formdata['nameRo'])
                ->setDescription($formdata['description'])
                ->setStream($stream)
                ->setDateModified(new \DateTime())
                ->setStudentNo($formdata['studentNo'])
                ->setRemainingStudentNo(
                    min($project->getRemainingStudentNo(), $formdata['studentNo'])
                );

            if (!$project->getDateCreated()) {
                $project->setDateCreated(new \DateTime());
            }

            if (!$project->getProfessor()) {
                $project->setProfessor($user);
            }

            $currentTags = [];
            if (!UtilService::isNullObject($project->getTagProjects()) && count($project->getTagProjects())) {
                foreach ($project->getTagProjects() as $tagProject) {
                    $currentTags[] = $tagProject->getTag()->getId();
                    if (!in_array($tagProject->getTag()->getId(), array_values($formdata['tags']))) {
                        $project->removeTagProject($tagProject);
                        $em->remove($tagProject);
                    }
                }
            }

            $newTags = array_diff(array_values($formdata['tags']), $currentTags);
            foreach ($newTags as $tag) {
                /** @var Tag $tag */
                $tag = $em->getRepository('AppBundle:Tag')->find($tag);
                $tagProject = new TagProject();
                $tagProject->setProject($project)
                    ->setTag($tag);
                $project->addTagProject($tagProject);
                $em->persist($tagProject);
            }

            $em->persist($project);
            $em->flush();
            $em->getConnection()->commit();
        } catch(\Exception $e) {
            $em->getConnection()->rollBack();
            return $e->getMessage();
        }

        return null;
    }

    public function getReportFormData()
    {
        return [
            'reports' => [
                ProjectStudent::REPORT_YES_STUDENTS => $this->translator->trans('reports.good_report'),
                ProjectStudent::REPORT_NO_STUDENTS => $this->translator->trans('reports.bad_report')
            ]
        ];
    }

    /**
     * @return array
     */
    public function getReportChartStatistics()
    {
        /** @var ProjectStudentRepository $projectStudentRepository */
        $projectStudentRepository = $this->getDoctrine()->getRepository('AppBundle:ProjectStudent');

        $statistics = $projectStudentRepository->getReportChartStatistics();

        $count = 0;

        $result = [
            'total' => 0,
            'acceptedNo' => 0,
            'rejectedNo' => 0,
            'pendingNo' => 0,
            'invalidatedNo' => 0
        ];

        foreach ($statistics as $statistic) {
            $count += $statistic['total'];

            switch ($statistic['status']) {
                case ProjectStudent::STATUS_ACCEPTED:
                    $result['acceptedNo'] = $statistic['total'];
                    break;
                case ProjectStudent::STATUS_REJECTED:
                    $result['rejectedNo'] = $statistic['total'];
                    break;
                case ProjectStudent::STATUS_PENDING:
                    $result['pendingNo'] = $statistic['total'];
                    break;
                case ProjectStudent::STATUS_INVALIDATED:
                    $result['invalidatedNo'] = $statistic['total'];
                    break;
                default:
                    break;
            }
        }

        $result['total'] = $count;

        var_dump($result);

        return $result;
    }
}