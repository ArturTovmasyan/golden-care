<?php

namespace App\Util;

use App\Api\V1\Common\Service\ConfigService;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Mailer
{
    /**
     * Default params
     */
    const FROM = 'support@seniorcaresw.com';
    const TO = 'support@seniorcaresw.com';
    const BODY = 'text/html';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $vars = [];

    /**
     * @var string
     */
    private $recipient;

    /**
     * @var array
     */
    private $bcc = [];

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $template;

    /**
     * Mailer constructor.
     * @param ContainerInterface $container
     * @param ConfigService $configService
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, ConfigService $configService, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->configService = $configService;
        $this->logger = $logger;
    }

    /**
     * @param array $vars
     * @return $this
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;

        return $this;
    }

    /**
     * @param string $recipient
     * @return $this
     */
    public function setRecipient(string $recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @param array $bcc
     * @return $this
     */
    public function setBcc(array $bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function send()
    {
        if (empty($this->recipient)) {
            throw new \Exception('Email recipient not defined');
        }

        if (empty($this->template)) {
            throw new \Exception('Email template not defined');
        }

        if (empty($this->subject)) {
            throw new \Exception('Email subject not defined');
        }

        $body = $this->container->get('twig')->render(
            $this->template,
            $this->vars
        );

        $mailer = $this->container->get('mailer');
        $message = (new \Swift_Message($this->subject))
            ->setFrom(self::FROM)
            ->setTo($this->recipient)
            ->setBcc($this->bcc)
            ->setBody($body, self::BODY);

        return $mailer->send($message);
    }

    /**
     * @param User $user
     * @return mixed
     * @throws \Exception
     */
    public function notifyCredentials(User $user)
    {
        if (!$user) {
            throw new \Exception('Email has dependency on user');
        }

        return $this
            ->setRecipient($user->getEmail())
            ->setTemplate('@api_email/credentials.html.twig')
            ->setSubject('Sign In Details')
            ->setVars([
                'subject' => $this->subject,
                'user' => $user,
            ])
            ->send();
    }

    /**
     * @param User $user
     * @param $baseUrl
     * @return bool|mixed
     * @throws \Exception
     */
    public function sendPasswordRecoveryLink(User $user, $baseUrl)
    {
        if (!$user) {
            throw new \Exception('Email has dependency on user');
        }

        return $this
            ->setRecipient($user->getEmail())
            ->setTemplate('@api_email/password-recovery.html.twig')
            ->setSubject('Password Recovery')
            ->setVars([
                'subject' => $this->subject,
                'user' => $user,
                'baseUrl' => $baseUrl,
            ])
            ->send();
    }

    /**
     * @param User $user
     * @param $baseUrl
     * @return bool|mixed
     * @throws \Exception
     */
    public function sendActivationLink(User $user, $baseUrl)
    {
        if (!$user) {
            throw new \Exception('Email has dependency on user');
        }

        return $this
            ->setRecipient($user->getEmail())
            ->setTemplate('@api_email/activation.html.twig')
            ->setSubject('Welcome to SeniorCare')
            ->setVars([
                'subject' => $this->subject,
                'user' => $user,
                'baseUrl' => $baseUrl,
            ])
            ->send();
    }

    /**
     * @param $email
     * @param $baseUrl
     * @param $token
     * @param $fullName
     * @return mixed
     * @throws \Exception
     */
    public function inviteUser($email, $baseUrl, $token, $fullName)
    {
        return $this
            ->setRecipient($email)
            ->setTemplate('@api_email/invitation.html.twig')
            ->setSubject('Invite to Space')
            ->setVars([
                'subject' => $this->subject,
                'baseUrl' => $baseUrl,
                'token' => $token,
                'fullName' => $fullName,
            ])
            ->send();
    }

    /**
     * @param $email
     * @param $domain
     * @param $fullName
     * @param $password
     * @return mixed
     * @throws \Exception
     */
    public function createCustomer($email, $domain, $fullName, $password)
    {
        return $this
            ->setRecipient($email)
            ->setTemplate('@api_email/create-customer.html.twig')
            ->setSubject('Welcome to SeniorCare Software!')
            ->setVars([
                'subject' => $this->subject,
                'domain' => $domain,
                'fullName' => $fullName,
                'email' => $email,
                'password' => $password
            ])
            ->send();
    }

    /**
     * @param $email
     * @param $domain
     * @param $token
     * @param $fullName
     * @return mixed
     * @throws \Exception
     */
    public function inviteCustomer($email, $domain, $token, $fullName)
    {
        return $this
            ->setRecipient($email)
            ->setTemplate('@api_email/invite-customer.html.twig')
            ->setSubject('Invite Customer to Space')
            ->setVars([
                'subject' => $this->subject,
                'domain' => $domain,
                'token' => $token,
                'fullName' => $fullName,
            ])
            ->send();
    }

    /**
     * @param $emails
     * @param $subject
     * @param $body
     * @param $spaceName
     * @return mixed
     */
    public function sendNotification($emails, $subject, $body, $spaceName)
    {
        $mailer = $this->container->get('mailer');
        $message = (new \Swift_Message($subject))
            ->setFrom(self::FROM)
            ->setBcc($emails)
            ->setBody($body, self::BODY);

        $status = $mailer->send($message);

        $this->logger->critical($subject, array(
            'status' => $status,
            'space' => $spaceName,
            'emails' => $emails
        ));

        return $status;
    }

    /**
     * @param $emails
     * @param $subject
     * @param $body
     * @param $path
     * @param $spaceName
     * @return mixed
     */
    public function sendReportNotification($emails, $subject, $body, $path, $spaceName)
    {
        $mailer = $this->container->get('mailer');
        $message = (new \Swift_Message($subject))
            ->setFrom(self::FROM)
            ->setBcc($emails)
            ->setBody($body, self::BODY)
            ->attach(\Swift_Attachment::fromPath($path));

        $status = $mailer->send($message);

        $this->logger->critical($subject, array(
            'status' => $status,
            'space' => $spaceName,
            'emails' => $emails
        ));

        return $status;
    }

    /**
     * @param $user
     * @param $facilityNames
     * @param $customer
     * @param $subject
     * @param $body
     * @return mixed
     */
    public function sendHandledCustomerException($user, $facilityNames, $customer, $subject, $body)
    {
        $bcc = \preg_split('/[\s,]+/', $this->configService->get('EXCEPTION_RECIPIENTS'))
            ?? ['armenv@intermotionllc.com'];

        return $this
            ->setRecipient(self::TO)
            ->setBcc($bcc)
            ->setTemplate('@api_email/handle-customer-exception.html.twig')
            ->setSubject($subject)
            ->setVars([
                'user' => $user,
                'facilities' => $facilityNames,
                'customer' => $customer,
                'subject' => $subject,
                'body' => $body,
            ])
            ->send();
    }

    /**
     * @param $emails
     * @param $subject
     * @param $body
     * @param $spaceName
     * @return mixed
     */
    public function sendDocumentNotification($emails, $subject, $body, $spaceName)
    {
        $mailer = $this->container->get('mailer');
        $message = (new \Swift_Message($subject))
            ->setFrom(self::FROM)
            ->setBcc($emails)
            ->setBody($body, self::BODY);

        $status = $mailer->send($message);

        $this->logger->critical($subject, array(
            'status' => $status,
            'space' => $spaceName,
            'emails' => $emails
        ));

        return $status;
    }

    /**
     * @param $emails
     * @param $subject
     * @param $body
     * @param $spaceName
     * @return mixed
     */
    public function sendLeadResidentNotification($emails, $subject, $body, $spaceName)
    {
        $mailer = $this->container->get('mailer');
        $message = (new \Swift_Message($subject))
            ->setFrom(self::FROM)
            ->setBcc($emails)
            ->setBody($body, self::BODY);

        $status = $mailer->send($message);

        $this->logger->critical($subject, array(
            'status' => $status,
            'space' => $spaceName,
            'emails' => $emails
        ));

        return $status;
    }
}
