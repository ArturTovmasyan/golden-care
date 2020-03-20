<?php

namespace App\Command;

use App\Util\Mailer;
use PHPHtmlParser\Dom;
use SSilence\ImapClient\ImapClient as Imap;
use SSilence\ImapClient\IncomingMessage;
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

//        $render = '@api_zapier/template3.html.twig';
//        $body = $this->container->get('templating')->render($render, []);
//        dd($this->grabData('from', 'New submission from Book a Tour', $body));

        try {
            // TODO(vsarmen): move change config to expected email address
            $mailbox = 'imap.gmail.com';
            $username = 'webcontactforms@ciminocare.com';
            $password = 'WCJPeavey!13';
            $encryption = Imap::ENCRYPT_SSL; // or ImapClient::ENCRYPT_SSL or ImapClient::ENCRYPT_TLS or null

            $imap = new Imap($mailbox, $username, $password, $encryption);

            $imap->selectFolder('INBOX');
            $emails = $imap->getUnreadMessages(false);

            /** @var IncomingMessage $email */
            foreach ($emails as $email) {
                $from = $email->header->from;
                $subject = $email->header->subject;
                $body = $email->message->html->body;

                $data = $this->grabData($from, $subject, $body);

                if ($data !== null) {
//                    $imap->setSeenMessage($email->getID());

                    // TODO(vsarmen): add call to service
                    dump($data);
                }
            }
        } catch (\Throwable $t) {
            $output->writeln($t->getMessage());
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

}