<?php

namespace App\Command;

use App\Util\Mailer;
use Exception;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Google_Service_Gmail_MessagePart;
use Google_Service_Gmail_ModifyMessageRequest;
use PHPHtmlParser\Dom;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WebLeadGrabberCommand extends Command
{
    use LockableTrait;

    protected $grantService;

    /** @var Mailer */
    private $mailer;

    /** @var ContainerInterface */
    private $container;

    public function __construct(
        Mailer $mailer,
        ContainerInterface $container
    )
    {
        $this->mailer = $mailer;
        $this->container = $container;

        parent::__construct();
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:webleadgrabber')
            ->setDescription('Zapier email.')
            ->setHelp('This command allows you to send zapier email...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 1;
        }

        try {
            // Get the API client and construct the service object.
            $client = $this->getClient();
            $service = new Google_Service_Gmail($client);

            $user = 'me';
            $results = $service->users_messages->listUsersMessages($user, ['q' => 'IN:INBOX IS:UNREAD']);

            if (count($results->getMessages()) == 0) {
                print "No messages found.\n";
            } else {
                print "Messages:\n";
                foreach ($results->getMessages() as $message_info) {
                    $message = $service->users_messages->get($user, $message_info->getId());

                    $from = $this->getMessageHeader($message->getPayload()->getHeaders(), 'From');
                    $subject = $this->getMessageHeader($message->getPayload()->getHeaders(), 'Subject');
                    $body = $this->getMessageBody($message);

                    $data = $this->grabData($from, $subject, $body);

                    if ($data !== null) {
                        $output->writeln(sprintf("ID - %s, Subject - %s\n", $message_info->getId(), $subject));
//                        $this->markRead($user, $service, $message_info->getId());

                        // TODO(vsarmen): Armen add service call here
                        dump($data);
                    }
                }
            }
        } catch (\Throwable $t) {
            $output->writeln($t->getMessage());
            $output->writeln($t->getTraceAsString());
        }

        $this->release();

        return 0;
    }

    /**
     * @param string $from
     * @param string $subject
     * @param string $message
     * @return array|null
     * @throws \Throwable
     */
    private function grabData(string $from, string $subject, string $message): ?array
    {
        $known_subjects = [
            'New submission from Book a Tour',
            'New submission from Contact Form'
        ];

        if (!\in_array($subject, $known_subjects)) {
            return null;
        } else {
            $dom = new Dom();
            $dom->load($message);
            $table = $dom->find('tr > td > table');

            $data = ['From' => $from, 'Subject' => $subject];

            if ($table->count() > 0) {
                /** @var Dom\Collection $tds */
                $tds = $table[0]->find('tr > td');

                for ($i = 0; $i < $tds->count(); $i += 3) {
                    $header = strip_tags($tds[$i]->find('strong')->innerHTML);
                    $value = preg_replace('<br\s*/?>', "\r\n", $tds[$i + 2]->innerHTML);
                    $data[$header] = trim(strip_tags($value));
                }
            }

            return $data;
        }
    }

    /**
     * @return Google_Client
     * @throws \Throwable
     */
    private function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('Gmail API PHP Quickstart');
        $client->setScopes(Google_Service_Gmail::GMAIL_MODIFY);
        $client->setAuthConfig('gmail_client_id.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file gmail_token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'gmail_token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }

    private function getMessageHeader($headers, $name)
    {
        foreach ($headers as $header) {
            if ($header['name'] == $name) {
                return $header['value'];
            }
        }
    }

    /**
     * @param Google_Service_Gmail_Message $message
     * @return bool|false|string
     * @throws \Throwable
     */
    private function getMessageBody($message)
    {
        $payload = $message->getPayload();

        if ($result = $this->decodeMessageBody($payload->getBody()->getData())) {
            return $result;
        }

        return $this->decodeMessageParts($payload->getParts());
    }

    /**
     * @param $service
     * @param $messageId
     * @throws \Throwable
     */
    private function markRead($user, $service, $messageId)
    {
        $mods = new Google_Service_Gmail_ModifyMessageRequest();
        $mods->setRemoveLabelIds(['UNREAD']);
        $service->users_messages->modify($user, $messageId, $mods);
    }

    /**
     * @param $body
     * @return bool|false|string
     * @throws \Throwable
     */
    private function decodeMessageBody($body)
    {
        $rawData = $body;
        $sanitizedData = strtr($rawData, '-_', '+/');
        $decodedMessage = base64_decode($sanitizedData);
        if (!$decodedMessage)
            return false;

        return $decodedMessage;
    }

    /**
     * @param Google_Service_Gmail_MessagePart|Google_Service_Gmail_MessagePart[] $parts
     * @return bool|false|string
     * @throws \Throwable
     */
    private function decodeMessageParts($parts)
    {
        foreach ($parts as $part) {
            if ($part->getMimeType() === 'text/html' && $part->getBody())
                if ($result = $this->decodeMessageBody($part->getBody()->getData()))
                    return $result;
        }

        /** @var Google_Service_Gmail_MessagePart $part */
        foreach ($parts as $part) {
            if ($result = $this->decodeMessageParts($part->getParts()))
                return $result;
        }
    }

}