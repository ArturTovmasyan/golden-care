<?php
namespace App\Api\V1\Common\Service;

use App\Entity\User;
use App\Entity\UserPhone;
use App\Model\Phone;
use Aws\Exception\AwsException;
use Aws\Sns\SnsClient;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AmazonSnsService
 * @package App\Api\V1\Common\Service
 */
class AmazonSnsService
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * AmazonSnsService constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * @param User $user
     * @param $message
     */
    public function sendMessageToUser(User $user, $message): void
    {
        $snSClient = new SnsClient([
            'region' => getenv('AWS_REGION'),
            'version' => getenv('AWS_VERSION'),
            'credentials' => [
                'key' => getenv('AWS_KEY'),
                'secret' => getenv('AWS_SECRET'),
                'region' => getenv('AWS_REGION'),
            ],
        ]);

        $topicArn = $this->manageTopic($snSClient, $user);
        if ($topicArn) {
            try {
                $snSClient->publish([
                    'TopicArn' => $topicArn,
                    'Message' => $message,
                ]);
            } catch (AwsException $e) {
                throw $e;
            }
        }
    }

    /**
     * @param SnsClient $snSClient
     * @param User $user
     * @return null|string
     * @throws \Exception
     */
    public function manageTopic(SnsClient $snSClient, User $user): ?string
    {
        $topicArn = $user->getTopic();

        try {
            $phones = [];

            /** @var UserPhone $phone */
            foreach ($user->getPhones() as $phone) {
                if ($phone->getCompatibility() === Phone::US_COMPATIBLE && $phone->isSmsEnabled()) {
                    $phones[] = $phone->getNumber();
                }
            }

            if (!empty($phones)) {
                if (!$topicArn) {
                    /** @var mixed $topic */
                    $topic = $snSClient->createTopic([
                        'Name' => 'SCDB_' . $user->getId() . '_' . uniqid('', false),
                        'DisplayName' => 'Seniorcare',
                    ]);
                    $topicArn = $topic['TopicArn'];

                    try {
                        $this->em->getConnection()->beginTransaction();

                        $user->setTopic($topicArn);

                        $this->em->persist($user);
                        $this->em->flush();

                        $this->em->getConnection()->commit();
                    } catch (\Exception $e) {
                        $this->em->getConnection()->rollBack();

                        throw $e;
                    }

                    $snSClient->setTopicAttributes(array(
                        'TopicArn' => $topicArn,
                        'AttributeName' => 'DisplayName',
                        'AttributeValue' => 'Seniorcare',
                    ));
                }

                /** @var mixed $subscriptions */
                $subscriptions = $snSClient->listSubscriptionsByTopic(array(
                    'TopicArn' => $topicArn
                ));
                $endpointsFromSubscriptions = array_map(function($item){return str_replace('+', '', $item['Endpoint']);} , $subscriptions['Subscriptions']);

                $endpointsFromUser = [];
                foreach ($phones as $phone) {
                    // Formatting number from (123) 123-1234 to 1-123-123-1234.
                    $endPoint = '1-' . str_replace(') ', '-', str_replace('(', '', $phone));
                    $endPointClean = str_replace('-', '', $endPoint);
                    $endpointsFromUser[] = '+' . $endPointClean;

                    if (!\in_array($endPointClean, $endpointsFromSubscriptions, false)) {
                        $snSClient->subscribe([
                            'TopicArn' => $topicArn,
                            'Protocol' => 'sms',
                            'Endpoint' => $endPoint
                        ]);
                    }
                }

                foreach ($subscriptions->get('Subscriptions') as $subscription) {
                    if (!\in_array($subscription['Endpoint'], $endpointsFromUser, false)) {
                        $this->unSubscribe($snSClient, $subscription['SubscriptionArn']);
                    }
                }

                return $topicArn;
            }

            if (empty($phones) && $user->getTopic()) {
                $snSClient->deleteTopic([
                    'TopicArn' => $user->getTopic()
                ]);

                try {
                    $this->em->getConnection()->beginTransaction();

                    $user->setTopic(null);

                    $this->em->persist($user);
                    $this->em->flush();

                    $this->em->getConnection()->commit();
                } catch (\Exception $e) {
                    $this->em->getConnection()->rollBack();

                    throw $e;
                }
            }
        } catch (AwsException $e) {
            throw $e;
        }

        return null;
    }

    /***
     * @param SnsClient $snSClient
     * @param $subscriptionArn
     */
    private function unSubscribe(SnsClient $snSClient, $subscriptionArn): void
    {
        if ($subscriptionArn) {
            try {
                 $snSClient->unsubscribe([
                    'SubscriptionArn' => $subscriptionArn
                 ]);
            } catch (AwsException $e) {
                throw $e;
            }
        }
    }
}
