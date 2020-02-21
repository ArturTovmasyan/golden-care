<?php

namespace App\Api\V1\Common\Service;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImageFilterService
 * @package App\Api\V1\Common\Service
 */
class ImageFilterService
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
     * @var S3Service
     */
    protected $s3Service;

    /**
     * ImageFilterService constructor.
     * @param EntityManagerInterface $em
     * @param ContainerInterface $container
     * @param S3Service $s3Service
     */
    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container,
        S3Service $s3Service
    )
    {
        $this->em = $em;
        $this->container = $container;
        $this->s3Service = $s3Service;
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

        $binary = $filterManager->applyFilter($binary, $filters[1]);

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
