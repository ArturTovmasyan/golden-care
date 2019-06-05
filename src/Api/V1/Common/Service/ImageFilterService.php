<?php
namespace App\Api\V1\Common\Service;

use App\Api\V1\Common\Service\Exception\FileExtensionException;
use App\Api\V1\Common\Service\Exception\UnhandledImageOwnerException;
use App\Entity\Resident;
use App\Entity\ResidentImage;
use App\Entity\User;
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
    public const IMAGE_DEFAULT_TITLE = 'original';

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
     * @param $base64
     * @param $entity
     */
    public function createAllFilterVersion($base64, $entity): void
    {
        $filterService = $this->container->getParameter('filter_service');

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
        unset($filters[0]);

        //create cache versions for files
        foreach ($filters as $filter) {
            $data = $filterManager->applyFilter($binary, $filter)->getContent();
            if($data) {
                $base64 = 'data:image/' . $format . ';base64,' . base64_encode($data);

                if ($entity instanceof Resident) {
                    $filterImage = new ResidentImage();
                    $filterImage->setResident($entity);
                } elseif ($entity instanceof User) {
                    $filterImage = new UserImage();
                    $filterImage->setUser($entity);
                } else {
                    throw new UnhandledImageOwnerException();
                }

                $filterImage->setPhoto($base64);
                $filterImage->setTitle($filter);

                $this->em->persist($filterImage);
            }
        }
    }
}
