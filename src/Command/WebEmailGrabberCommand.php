<?php

namespace App\Command;

use App\Api\V1\Lead\Service\ActivityService;
use App\Api\V1\Lead\Service\LeadService;
use App\Api\V1\Lead\Service\WebEmailService;
use Exception;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Google_Service_Gmail_MessagePart;
use Google_Service_Gmail_ModifyMessageRequest;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PHPHtmlParser\Dom;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebEmailGrabberCommand extends Command
{
    use LockableTrait;

    /** @var WebEmailService */
    private $webEmailService;

    /** @var LeadService */
    private $leadService;

    /** @var ActivityService */
    private $activityService;

    public function __construct(
        WebEmailService $webEmailService, LeadService $leadService, ActivityService $activityService
    )
    {
        $this->webEmailService = $webEmailService;
        $this->leadService = $leadService;
        $this->activityService = $activityService;

        parent::__construct();
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:webemailgrabber')
            ->setDescription('Web email.')
            ->setHelp('This command allows you to save web email...')
            ->addArgument('domain', InputArgument::REQUIRED, 'The domain of the customer.');

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

            if (count($results->getMessages()) === 0) {
                print "No messages found.\n";
            } else {
                $this->leadService->setActivityService($this->activityService);

                print "Messages:\n";
                foreach ($results->getMessages() as $message_info) {
                    $message = $service->users_messages->get($user, $message_info->getId());

                    $from = $this->getMessageHeader($message->getPayload()->getHeaders(), 'From');
                    $subject = $this->getMessageHeader($message->getPayload()->getHeaders(), 'Subject');
                    $body = $this->getMessageBody($message);

                    $data = $this->grabData($from, $subject, $body);

                    if ($data !== null) {
                        $output->writeln(sprintf("ID - %s, Subject - %s\n", $message_info->getId(), $subject));
                        dump($data);

                        $protocol = getenv('APP_ENV') === 'prod' ? 'https://' : 'http://';
                        $baseUrl = $protocol . $input->getArgument('domain');

                        try {
                            $this->webEmailService->add($data);
                            $this->markRead($user, $service, $message_info->getId());
                            $this->leadService->addWebLeadFromCommand($data, $baseUrl);
                        } catch (\Throwable $ct) {
                            $output->writeln($ct->getMessage());
                        }
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
            'New submission from Book a Tour Today!',
            'New submission from Contact Form',
            'New submission from Contact Us',
            'New submission from Contact Us (Footer)',
            'New submission from Footer Form',
            'New Submission from CiminoCare Contact Form',
            'New Submission from Auburn Oaks Contact Form',
            'New Submission from Citrus Heights Terrace Contact Form',
            'New Submission from Burlingame Villa Contact Form',
            'New Submission from Country Club Manor Contact Form',
            'New Submission from Courtyard Terrace Contact Form',
            'New Submission from Fruitridge Villa Contact Form',
            'New Submission from Mills Estate Villa Contact Form',
            'New Submission from Orangeburg Manor Contact Form',
            'New Submission from Portola Gardens Contact Form',
            'New Submission from River Fountains of Lodi Contact Form',
            'New Submission from Walnut House Contact Form',
            'New Submission from Grand River Villa Contact Form',
            'New Submission from Camlu Assisted Living Community Contact Form',
            'New Form Entry: Camlu Contact Form',
            'New Submission from Orangeburg Manor Facebook Ad',
        ];

        $message_map = [
            'Message',
            'Comments and/or Special Requests',
            'How can we help you?',
            'How Can We Help You?:',
            'Questions or comments'
        ];

        $data = null;
        if (\in_array($subject, $known_subjects, false)) {
            $dom = new Dom();
            $dom->load($message);
            $table = $dom->find('tr > td > table');

            $data = ['From' => $from, 'Subject' => $subject];

            $messageKey = 'Message';
            if ($table->count() > 0) {
                if (stripos($subject, 'new form entry:') !== false) {
                    /** @var Dom\Collection $div */
                    $div = $table[0]->find('tr > td > table > tr > td > div > div');
                    $innerHtml = $div->innerHtml;
                    $innerHtmlArray = explode('</h2> <br />', $innerHtml);
                    $text = preg_replace('#<br\s*/?\s*>#', "\r\n", $innerHtmlArray[1]);
                    $text = trim(strip_tags($text));

                    $messageKeyForExplode = 'Questions or comments';
                    $data[$messageKey] = '';
                    if (stripos($text, $messageKeyForExplode) !== false) {
                        $textArray = explode($messageKeyForExplode, $text);

                        $textFirst = explode('First name, last name', $textArray[0]);
                        $textName = explode('Email', $textFirst[1]);
                        $data['Name'] = trim($textName[0]);
                        $data['Email'] = trim($textName[1]);

                        $textLast = explode('Phone', $textArray[1]);
                        $data[$messageKey] = $textLast[0];
                        if (stripos($textLast[1], 'choose one') !== false) {
                            $textPhone = explode('Choose one', $textLast[1]);
                            $data['Phone'] = trim($textPhone[0]);
                        } else {
                            $data['Phone'] = trim($textLast[1]);
                        }
                    }
                } else {
                    /** @var Dom\Collection $tds */
                    $tds = $table[0]->find('tr > td');

                    for ($i = 0; $i < $tds->count(); $i += 3) {
                        $header = strip_tags($tds[$i]->find('strong')->innerHTML);

                        if (\in_array($header, $message_map, false)) {
                            $header = $messageKey;
                        }

                        $value = preg_replace('#<br\s*/?\s*>#', "\r\n", $tds[$i + 2]->innerHTML);
                        $data[$header] = trim(strip_tags($value));
                    }
                }
            } else {
                $div = $dom->find('div[contains(@class, "a3s")]');
                /** @var Dom\Collection $datum */
                $datum = $div[0];

                if (stripos($subject, 'facebook ad') !== false) {
                    $text = $datum->text;

                    $textArray = explode('email:', $text);
                    $emailArray = explode(' name:', $textArray[1]);
                    $data['Email'] = $emailArray[0];
                    $nameArray = explode(' phone #:', $emailArray[1]);
                    $data['Name'] = $nameArray[0];
                    $data['Phone'] = $nameArray[1];
                    $data[$messageKey] = '';
                } else {
                    $parent = $datum->parent;
                    $innerHtml = $parent->innerHtml;

                    $innerHtmlArray = explode('---', $innerHtml);
                    $text = $innerHtmlArray[0];

                    $messageKeyForExplode = 'How Can We Help You?: ';
                    $data[$messageKey] = '';
                    if (stripos($text, $messageKeyForExplode) !== false) {
                        $textArray = explode($messageKeyForExplode, $text);

                        $message = preg_replace('#<br\s*/?\s*>#', "\r\n", $textArray[1]);
                        $data[$messageKey] = $message;

                        $keyValueArray = explode('<br />', $textArray[0]);

                        if (!empty($keyValueArray)) {
                            foreach ($keyValueArray as $item) {
                                $keyValue = explode(': ', $item);
                                if (count($keyValue) === 2) {
                                    $data[$keyValue[0]] = $keyValue[1];
                                }
                            }
                        }
                    }
                }
            }

            if (array_key_exists('First Name', $data) && array_key_exists('Last Name', $data)) {
                $data['Name'] = sprintf('%s %s', $data['First Name'], $data['Last Name']);

                unset($data['First Name']);
                unset($data['Last Name']);
            }

            if (array_key_exists('Phone Number', $data)) {
                $data['Phone'] = $data['Phone Number'];

                unset($data['Phone Number']);
            }

            if (array_key_exists($messageKey, $data)) {
                $data['Spam'] = $this->checkForSpam($data[$messageKey]);

                //if in Message exist words janitorial, janitor or qualityjanitorialservices42@gmail.com
                //then mark as spam
                if (strpos($data[$messageKey], 'janitorial') !== false || strpos($data[$messageKey], 'janitor') !== false || strpos($data[$messageKey], 'qualityjanitorialservices42@gmail.com') !== false) {
                    $data['Spam'] = true;
                }
            }

            if (stripos($subject, 'facebook ad') !== false) {
                $data['Spam'] = false;
            }
        }

        return $data;
    }

    /**
     * @return Google_Client
     * @throws \Throwable
     */
    private function getClient()
    {
        $path_prefix = '/srv/_vcs/backend/';
        $client = new Google_Client();
        $client->setApplicationName('Gmail API PHP Quickstart');
        $client->setScopes(Google_Service_Gmail::GMAIL_MODIFY);
        $client->setAuthConfig($path_prefix . 'gmail_client_id.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file gmail_token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = $path_prefix . 'gmail_token.json';
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
                    throw new Exception(implode(', ', $accessToken));
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
            if ($header['name'] === $name) {
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
            if ($part->getMimeType() === 'text/html' && $part->getBody()) {
                if ($result = $this->decodeMessageBody($part->getBody()->getData())) {
                    return $result;
                }
            }
        }

        /** @var Google_Service_Gmail_MessagePart $part */
        foreach ($parts as $part) {
            if ($result = $this->decodeMessageParts($part->getParts())) {
                return $result;
            }
        }
    }

    /**
     * @param string $message
     * @return bool|null
     * @throws \Throwable
     */
    private function checkForSpam($message): ?bool
    {
        $client = new Client();

        $response = $client->post('http://127.0.0.1:5000/check', [
            RequestOptions::JSON => ["message" => $message]
        ]);

        if ($response->getStatusCode() === 200) {
            $body = json_decode($response->getBody()->getContents(), true);

            if ($body !== null) {
                return $body['spam'] === 'spam' ? true : false;
            }
        }

        return null;
    }
}
