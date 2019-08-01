<?php
namespace App\Api\V1\Common\Service;

use App\Api\V1\Common\Service\Exception\FileExtensionException;
use App\Entity\File;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use DataURI\Parser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class S3Service
 * @package App\Api\V1\Common\Service
 */
class S3Service
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * S3Service constructor.
     * @param EntityManagerInterface $em
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container
    ) {
        $this->em        = $em;
        $this->container = $container;
    }

    /**
     * @return S3Client
     */
    public function getS3Client(): S3Client
    {
        $client = new S3Client([
            'region' => getenv('AWS_REGION'),
            'version' => getenv('AWS_VERSION'),
            'credentials' => [
                'key' => getenv('AWS_KEY'),
                'secret' => getenv('AWS_SECRET'),
            ],
        ]);

        return $client;
    }

    /**
     * @param File $file
     * @param $base64
     */
    public function uploadDocumentFile($file, $base64): void
    {
        $pdfFileService = $this->container->getParameter('pdf_file_service');

        $mimeTypeParts = explode('/', $file->getMimeType());
        $format = $mimeTypeParts[1];

        if (!\in_array($format, $pdfFileService['extensions'], false)) {
            throw new FileExtensionException();
        }

        try {
            $parseFile = Parser::parse($base64);

            $s3Id = $file->getId().'.'.$format;

            $this->getS3Client()->putObject([
                'Bucket'      => getenv('AWS_BUCKET'),
                'Key'         => $file->getType().'/'.$s3Id,
                'Body'        => $parseFile->getData(),
                'ContentType' => $file->getMimeType(),
                'ACL'         => 'public-read',
            ]);

            $file->setS3Id($s3Id);
            $this->em->persist($file);

        } catch (S3Exception $e) {
            throw $e;
        }
    }
}
