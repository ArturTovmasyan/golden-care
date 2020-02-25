<?php

namespace App\Command;

use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Common\Service\S3Service;
use App\Entity\Image;
use App\Entity\ResidentImage;
use App\Entity\UserImage;
use App\Model\FileType;
use App\Util\MimeUtil;
use DataURI\Parser;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrateResidentAndUserImagesToS3Command extends Command
{
    use LockableTrait;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var S3Service
     */
    protected $s3Service;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * InviteCustomerCommand constructor.
     * @param EntityManagerInterface $em
     * @param S3Service $s3Service
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $em, S3Service $s3Service, ContainerInterface $container)
    {
        parent::__construct();
        $this->em = $em;
        $this->s3Service = $s3Service;
        $this->container = $container;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:migrate-images')
            ->setDescription('Migrate Resident And User Images To Amazon S3.')
            ->setHelp('This command allows you migrate resident and user images to S3...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $residentImages = $this->em->getRepository(ResidentImage::class)->findAll();

            if (!empty($residentImages)) {
                /** @var ResidentImage $residentImage */
                foreach ($residentImages as $residentImage) {
                    $photo = $residentImage->getPhoto();

                    // save image
                    if ($photo !== null) {
                        $image = new Image();

                        $explodeData = explode(',', $photo);
                        $base64Image = base64_decode(end($explodeData));
                        $parseFile = Parser::parse($photo);
                        $mimeType = $parseFile->getMimeType();
                        if ($mimeType === 'image/jpg') {
                            $mimeType = 'image/jpeg';
                        }
                        $format = MimeUtil::mime2ext($mimeType);

                        $image->setMimeType($mimeType);
                        $image->setType(FileType::TYPE_RESIDENT_IMAGE);
                        $image->setResident($residentImage->getResident());
                        $image->setCreatedAt($residentImage->getCreatedAt());
                        $image->setUpdatedAt($residentImage->getUpdatedAt());
                        $image->setCreatedBy($residentImage->getCreatedBy());
                        $image->setUpdatedBy($residentImage->getUpdatedBy());

                        $this->em->persist($image);

                        $s3Id = $image->getId() . '.' . MimeUtil::mime2ext($image->getMimeType());
                        $image->setS3Id($s3Id);
                        $this->em->persist($image);

                        $this->s3Service->uploadFile($photo, $s3Id, $image->getType(), $image->getMimeType());

                        $this->createAllFilterVersion($image, $base64Image, $mimeType, $format);

                        $output->writeln('Resident image id: ' . $residentImage->getId());
                    }
                }
            }

            $userImages = $this->em->getRepository(UserImage::class)->findAll();

            if (!empty($userImages)) {
                /** @var UserImage $userImage */
                foreach ($userImages as $userImage) {
                    $photo = $userImage->getPhoto();

                    // save image
                    if ($photo !== null) {
                        $image = new Image();

                        $explodeData = explode(',', $photo);
                        $base64Image = base64_decode(end($explodeData));
                        $parseFile = Parser::parse($photo);
                        $mimeType = $parseFile->getMimeType();
                        if ($mimeType === 'image/jpg') {
                            $mimeType = 'image/jpeg';
                        }
                        $format = MimeUtil::mime2ext($mimeType);

                        $image->setMimeType($mimeType);
                        $image->setType(FileType::TYPE_AVATAR);
                        $image->setUser($userImage->getUser());
                        $image->setCreatedAt($userImage->getCreatedAt());
                        $image->setUpdatedAt($userImage->getUpdatedAt());
                        $image->setCreatedBy($userImage->getCreatedBy());
                        $image->setUpdatedBy($userImage->getUpdatedBy());

                        $this->em->persist($image);

                        $s3Id = $image->getId() . '.' . MimeUtil::mime2ext($image->getMimeType());
                        $image->setS3Id($s3Id);
                        $this->em->persist($image);

                        $this->s3Service->uploadFile($photo, $s3Id, $image->getType(), $image->getMimeType());

                        $this->createAllFilterVersion($image, $base64Image, $mimeType, $format);

                        $output->writeln('User image id: ' . $userImage->getId());
                    }
                }
            }

            $this->em->flush();

            $this->em->getConnection()->commit();

            $output->writeln('Successfully migrated');
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            if ($e instanceof ValidationException) {
                $output->writeln($e->getErrors());
            } else {
                $output->writeln($e->getMessage());
            }
        }

        $this->release();

        return 1;
    }

    /**
     * @param Image $image
     * @param $base64Image
     * @param $mimeType
     * @param $format
     */
    public function createAllFilterVersion($image, $base64Image, $mimeType, $format): void
    {
        //create binary
        $binary = new Binary($base64Image, $mimeType, $format);

        //create all filter images
        /** @var FilterManager $filterManager */
        $filterManager = $this->container->get('liip_imagine.filter.manager');

        //get all image filters
        $filters = $filterManager->getFilterConfiguration()->all();
        $filters = array_keys($filters);

        unset($filters[0], $filters[1]);

        //create cache versions for images
        foreach ($filters as $key => $filter) {
            $data = $filterManager->applyFilter($binary, $filter)->getContent();
            if ($data) {
                $base64 = 'data:image/' . $format . ';base64,' . base64_encode($data);

                if ($key === 2) {
                    $s3Id3535 = $image->getId() . '_35_35.' . $format;
                    $image->setS3Id3535($s3Id3535);

                    $this->s3Service->uploadFile($base64, $s3Id3535, $image->getType(), $image->getMimeType());
                } elseif ($key === 3) {
                    $s3Id150150 = $image->getId() . '_150_150.' . $format;
                    $image->setS3Id150150($s3Id150150);

                    $this->s3Service->uploadFile($base64, $s3Id150150, $image->getType(), $image->getMimeType());
                } elseif ($key === 4) {
                    $s3Id300300 = $image->getId() . '_300_300.' . $format;
                    $image->setS3Id300300($s3Id300300);

                    $this->s3Service->uploadFile($base64, $s3Id300300, $image->getType(), $image->getMimeType());
                }
            }
        }

        $this->em->persist($image);
    }
}