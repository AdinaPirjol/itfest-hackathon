<?php

namespace Project\AppBundle\Services;

use Project\AppBundle\Controller\DefaultController;
use Project\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;

class MailService
{
    const ID = 'app.mail';

    const TYPE_CREATE_ACCOUNT = 'create-account';
    const TYPE_RECOVER_PASSWORD = 'recover-password';
    const TYPE_PASSWORD_CHANGED = '';

    const TYPE_SEND_APPLICATION = 'apply-project';
    const TYPE_ACCEPT_APPLICATION = 'accept-project';
    const TYPE_REJECT_APPLICATION = 'reject-project';

    const TYPE_REMINDER_PROFESSOR_RESPOND = 'remind-professor';
    const TYPE_REMINDER_STUDENT_APPLY = 'remind-student';

    public static $mails = array(
        self::TYPE_CREATE_ACCOUNT => 'AppBundle:Email:createAccountMail.html.twig',
        self::TYPE_RECOVER_PASSWORD => 'AppBundle:Email:recoverPasswordMail.html.twig',
        self::TYPE_PASSWORD_CHANGED => '',

        self::TYPE_SEND_APPLICATION => 'AppBundle:Email:applyProjectMail.html.twig',
        self::TYPE_ACCEPT_APPLICATION => 'AppBundle:Email:acceptProjectMail.html.twig',
        self::TYPE_REJECT_APPLICATION => 'AppBundle:Email:rejectProjectMail.html.twig',

        self::TYPE_REMINDER_PROFESSOR_RESPOND => '',
        self::TYPE_REMINDER_STUDENT_APPLY => ''
    );

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var String
     */
    protected $authKey;

    /**
     * @var String
     */
    protected $from;

    /**
     * @var String
     */
    protected $subject;

    /**
     * @var String
     */
    protected $body;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function setFrom($from)
    {
        $this->from = $from;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public static function sendMail($subject, $from = null, $to, $body)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody(
                $body,
                'text/html'
            );

        $transport = \Swift_SmtpTransport::newInstance('smtp.googlemail.com', 465, 'ssl')
            ->setUsername('filsprofesor@gmail.com')
            ->setPassword('qzvjkldpngknbifw');

        $mailer = \Swift_Mailer::newInstance($transport);

        $mailer->send($message);
    }

    /**
     * @param User $user
     * @param $mailType
     * @param array $params
     *
     * @todo check if we need $from parameter or always the same??
     */
    public function sendMail2(User $user, $mailType, $params = array())
    {
        $to = $user->getUserCredentials()->getEmail();
        if ($this->container->get('kernel')->getEnvironment() == 'dev') {
            $to = $this->getContainer()->getParameter('to');
        }

        $this->setupMail(
            $user,
            self::$mails[$mailType],
            $params
        );

        $message = \Swift_Message::newInstance()
            ->setSubject($this->getSubject())
            ->setFrom($this->getFrom())
            ->setTo($to)
            ->setBody(
                $this->getBody(),
                'text/html'
            );

        $transport = \Swift_SmtpTransport::newInstance('smtp.googlemail.com', 465, 'ssl')
            ->setUsername($this->getFrom())
            ->setPassword($this->getAuthKey());

        $mailer = \Swift_Mailer::newInstance($transport);

        $mailer->send($message);
    }

    /**
     * @param User $user
     * @param string $twigTemplate
     * @param array $params
     */
    public function setupMail($user, $twigTemplate, array $params = array())
    {
        /** @var Translator $translator */
        $translator = $this->container->get('translator');
        $translator->setLocale($user->getPreferredLocale());

        $body = $this->container->get('templating')->render($twigTemplate, $params);

        $start = strpos($body, '<title>') + strlen('<title>');
        $end = strpos($body, '</title>');
        $subject = substr($body, $start, $end - $start);

        $this->setSubject($subject);
        $this->setBody($body);
    }
}