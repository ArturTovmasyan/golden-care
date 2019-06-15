<?php

namespace App\Util;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Mailer
{
    /**
     * Default params
     */
    const FROM = 'noreply@seniorcare.com';
    const BODY = 'text/html';

    /**
     * @var ContainerInterface
     */
    private $container;

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
    private $bbc = [];

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
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
     * @param array $bbc
     * @return $this
     */
    public function setBbc(array $bbc)
    {
        $this->bbc = $bbc;

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

        $mailer  = $this->container->get('mailer');
        $message = (new \Swift_Message($this->subject))
            ->setFrom(self::FROM)
            ->setTo($this->recipient)
            ->setBcc($this->bbc)
            ->setBody($body, self::BODY)
        ;

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
            ->setTemplate("@api_email/credentials.html.twig")
            ->setSubject('Sign In Details')
            ->setVars([
                'subject' => $this->subject,
                'user'    => $user,
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
            ->setTemplate("@api_email/password-recovery.html.twig")
            ->setSubject('Password Recovery')
            ->setVars([
                'subject' => $this->subject,
                'user'    => $user,
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
            ->setTemplate("@api_email/activation.html.twig")
            ->setSubject('Welcome to SeniorCare')
            ->setVars([
                'subject' => $this->subject,
                'user'    => $user,
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
                'subject'  => $this->subject,
                'baseUrl'  => $baseUrl,
                'token'    => $token,
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
                'subject'  => $this->subject,
                'domain'  => $domain,
                'fullName'  => $fullName,
                'email'  => $email,
                'password'  => $password
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
                'subject'  => $this->subject,
                'domain'  => $domain,
                'token'    => $token,
                'fullName' => $fullName,
            ])
            ->send();
    }

    /**
     * @param $emails
     * @param $subject
     * @param $body
     * @return mixed
     */
    public function sendNotification($emails, $subject, $body)
    {
        $mailer  = $this->container->get('mailer');
        $message = (new \Swift_Message($subject))
            ->setFrom(self::FROM)
            ->setBcc($emails)
            ->setBody($body, self::BODY)
        ;

        return $mailer->send($message);
    }
}
