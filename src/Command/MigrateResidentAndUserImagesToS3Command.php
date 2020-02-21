<?php

namespace App\Command;

use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Common\Service\ImageFilterService;
use App\Api\V1\Common\Service\S3Service;
use App\Entity\Image;
use App\Entity\ResidentImage;
use App\Entity\UserImage;
use App\Model\FileType;
use App\Util\MimeUtil;
use DataURI\Parser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var ImageFilterService
     */
    protected $imageFilterService;

    /**
     * InviteCustomerCommand constructor.
     * @param EntityManagerInterface $em
     * @param S3Service $s3Service
     * @param ImageFilterService $imageFilterService
     */
    public function __construct(EntityManagerInterface $em, S3Service $s3Service, ImageFilterService $imageFilterService)
    {
        parent::__construct();
        $this->em = $em;
        $this->s3Service = $s3Service;
        $this->imageFilterService = $imageFilterService;
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

                        $parseFile = Parser::parse($photo);
                        $base64Image = $parseFile->getData();
                        $mimeType = $parseFile->getMimeType();
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

                        $this->imageFilterService->createAllFilterVersion($image, $base64Image, $mimeType, $format);
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

                        $parseFile = Parser::parse($photo);
                        $base64Image = $parseFile->getData();
                        $mimeType = $parseFile->getMimeType();
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

                        $this->imageFilterService->createAllFilterVersion($image, $base64Image, $mimeType, $format);
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
}