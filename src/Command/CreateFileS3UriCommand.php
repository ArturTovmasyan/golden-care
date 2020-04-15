<?php

namespace App\Command;

use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Common\Service\S3Service;
use App\Entity\File;
use App\Entity\Image;
use App\Repository\FileRepository;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateFileS3UriCommand extends Command
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
     * @var GrantService
     */
    protected $grantService;

    /**
     * InviteCustomerCommand constructor.
     * @param EntityManagerInterface $em
     * @param S3Service $s3Service
     * @param GrantService $grantService
     */
    public function __construct(EntityManagerInterface $em, S3Service $s3Service, GrantService $grantService)
    {
        parent::__construct();
        $this->em = $em;
        $this->s3Service = $s3Service;
        $this->grantService = $grantService;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('app:create-file-s3-uri')
            ->setDescription('Create file S3 URI.')
            ->setHelp('This command allows you create file S3 URI...');
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

            return 1;
        }

        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ImageRepository $imageRepo */
            $imageRepo = $this->em->getRepository(Image::class);

            $images = $imageRepo->findAll();

            if (!empty($images)) {
                /** @var Image $image */
                foreach ($images as $image) {
                    $s3Uri_150_150 = $this->s3Service->getFile($image->getS3Id150150(), $image->getType());

                    $image->setS3Uri150150($s3Uri_150_150);

                    $s3Uri = $this->s3Service->getFile($image->getS3Id(), $image->getType());

                    $image->setS3Uri($s3Uri);

                    $image->setUpdatedBy($image->getCreatedBy());

                    $this->em->persist($image);
                }
            }

            /** @var FileRepository $fileRepo */
            $fileRepo = $this->em->getRepository(File::class);

            $files = $fileRepo->findAll();

            if (!empty($files)) {
                /** @var File $file */
                foreach ($files as $file) {
                    $s3Uri = $this->s3Service->getFile($file->getS3Id(), $file->getType());

                    $file->setS3Uri($s3Uri);

                    $file->setUpdatedBy($file->getCreatedBy());

                    $this->em->persist($file);
                }
            }

            $this->em->flush();

            $this->em->getConnection()->commit();

            $output->writeln('File(s) S3 URI successfully created');
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            if ($e instanceof ValidationException) {
                $output->writeln($e->getErrors());
            } else {
                $output->writeln($e->getMessage());
            }
        }

        $this->release();

        return 0;
    }
}