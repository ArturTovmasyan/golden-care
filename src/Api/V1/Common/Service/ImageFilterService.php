<?php
namespace App\Api\V1\Common\Service;

use App\Api\V1\Common\Service\Exception\FileExtensionException;
use App\Entity\HealthInsuranceFile;
use App\Entity\ResidentImage;
use App\Entity\UserImage;
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
     * ImageFilterService constructor.
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
     * @param ResidentImage|UserImage $image
     */
    public function createAllFilterVersion($image): void
    {
        $filterService = $this->container->getParameter('filter_service');

        $base64 = $image->getPhoto();

        $base64Items = explode(';base64,', $base64);
        $base64Image = $base64Items[1];

        $base64FirstPart = explode(':', $base64Items[0]);
        $mimeType = $base64FirstPart[1];

        $mimeTypeParts = explode('/', $mimeType);
        $format = $mimeTypeParts[1];

        if (!\in_array($format, $filterService['extensions'], false)) {
            throw new FileExtensionException();
        }

        //create binary
        $binary = new Binary(base64_decode($base64Image), $mimeType, $format);

        //create all filter images
        /** @var FilterManager $filterManager */
        $filterManager = $this->container->get('liip_imagine.filter.manager');

        //get all image filters
        $filters = $filterManager->getFilterConfiguration()->all();
        $filters = array_keys($filters);

        $binary = $filterManager->applyFilter($binary, $filters[1]);

        unset($filters[0], $filters[1]);

        //create cache versions for files
        foreach ($filters as $key => $filter) {
            $data = $filterManager->applyFilter($binary, $filter)->getContent();
            if($data) {
                $base64 = 'data:image/' . $format . ';base64,' . base64_encode($data);

                if($key === 2) {
                    $image->setPhoto3535($base64);
                } elseif ($key === 3) {
                    $image->setPhoto150150($base64);
                } elseif ($key === 4) {
                    $image->setPhoto300300($base64);
                }
            }
        }

        $this->em->persist($image);
    }

    /**
     * @param HealthInsuranceFile $file
     */
    public function validateFile($file): void
    {
        $filterService = $this->container->getParameter('filter_service');
        $fileService = $this->container->getParameter('file_service');

        $firstBase64 = $file->getFirstFile();
        $secondBase64 = $file->getSecondFile();

        $firstBase64Items = explode(';base64,', $firstBase64);
        $secondBase64Items = explode(';base64,', $secondBase64);

        $firstBase64FirstPart = explode(':', $firstBase64Items[0]);
        $secondBase64FirstPart = explode(':', $secondBase64Items[0]);

        $firstMimeType = $firstBase64FirstPart[1];
        $secondMimeType = $secondBase64FirstPart[1];

        $firstMimeTypeParts = explode('/', $firstMimeType);
        $firstFormat = $firstMimeTypeParts[1];

        $secondMimeTypeParts = explode('/', $secondMimeType);
        $secondFormat = $secondMimeTypeParts[1];

        if (($firstMimeType === 'application/pdf' && !\in_array($firstFormat, $fileService['extensions'], false))
            || ($secondMimeType === 'application/pdf' && !\in_array($secondFormat, $fileService['extensions'], false))) {
            throw new FileExtensionException();
        }

        if (($firstMimeType !== 'application/pdf' && !\in_array($firstFormat, $filterService['extensions'], false))
            || ($secondMimeType !== 'application/pdf' && !\in_array($secondFormat, $filterService['extensions'], false))) {
            throw new FileExtensionException();
        }
    }
}
