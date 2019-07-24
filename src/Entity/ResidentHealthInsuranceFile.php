<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use DataURI\Data;
use DataURI\Dumper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class ResidentHealthInsuranceFile
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentHealthInsuranceFileRepository")
 * @ORM\Table(name="tbl_resident_health_insurance_file")
 */
class ResidentHealthInsuranceFile
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_health_insurance_file_list",
     *     "api_admin_resident_health_insurance_file_get",
     *     "api_admin_resident_health_insurance_list",
     *     "api_admin_resident_health_insurance_get"
     * })
     */
    private $id;

    /**
     * @var ResidentHealthInsurance
     * @Assert\NotNull(message = "Please select a Health Insurance", groups={
     *     "api_admin_resident_health_insurance_file_add",
     *     "api_admin_resident_health_insurance_file_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\ResidentHealthInsurance", inversedBy="file")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident_health_insurance", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_health_insurance_file_list",
     *     "api_admin_resident_health_insurance_file_get"
     * })
     */
    private $insurance;

    /**
     * @var string|resource $firstFile
     * @ORM\Column(name="first_file", type="blob", nullable=true)
     */
    private $firstFile;

    /**
     * @var string|resource $secondFile
     * @ORM\Column(name="second_file", type="blob", nullable=true)
     */
    private $secondFile;

//    /**
//     * @Serializer\VirtualProperty()
//     * @Serializer\SerializedName("first_file")
//     * @Serializer\Groups({"api_admin_resident_health_insurance_get", "api_admin_resident_health_insurance_list"})
//     */
//    public function getFirst()
//    {
//        if(!empty($this->getFirstFile())) {
//            $data = stream_get_contents($this->getFirstFile());
//            $file_info = new \finfo(FILEINFO_MIME_TYPE);
//
//            return Dumper::dump(new Data($data, $file_info->buffer($data)));
//        }
//
//        return null;
//    }
//
//    /**
//     * @Serializer\VirtualProperty()
//     * @Serializer\SerializedName("second_file")
//     * @Serializer\Groups({"api_admin_resident_health_insurance_get", "api_admin_resident_health_insurance_list"})
//     */
//    public function getSecond()
//    {
//        if(!empty($this->getSecondFile())) {
//            $data = stream_get_contents($this->getSecondFile());
//            $file_info = new \finfo(FILEINFO_MIME_TYPE);
//
//            return Dumper::dump(new Data($data, $file_info->buffer($data)));
//        }
//
//        return null;
//    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("single_file")
     * @Serializer\Groups({"api_admin_resident_health_insurance_get", "api_admin_resident_health_insurance_list"})
     *
     * @throws \ImagickException
     * @throws \Exception
     */
    public function getSingleFile() {
        $first = $this->getFirstFile();
        $second = $this->getSecondFile();

        $img = new \Imagick();
        $img->setResolution(300, 300);
        $img->setCompression(\Imagick::COMPRESSION_JPEG);
        $img->setCompressionQuality(100);

        if(!empty($first)) {
            $img1 = new \Imagick();
            $img1->setResolution(300, 300);
            $img1->readImageBlob(stream_get_contents($first));

            $img->addImage($img1);
        }

        if(!empty($second)) {
            $img2 = new \Imagick();
            $img2->setResolution(300, 300);
            $img2->readImageBlob(stream_get_contents($second));

            $img->addImage($img2);
        }

        $random_name = '/tmp/hif_' . md5($this->id) . '_' . md5((new \DateTime())->format('Ymd_His'));
        $img->setImageFormat('pdf');
        $img->writeImages($random_name, true);

        $output = null;

        if(file_exists($random_name)) {
            $output = file_get_contents($random_name);
            unlink($random_name);
        }

        if(!empty($output)) {
            $output = 'data:application/pdf;base64,' . base64_encode($output);
        } else {
            $output = null;
        }

        return $output;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return ResidentHealthInsurance|null
     */
    public function getInsurance(): ?ResidentHealthInsurance
    {
        return $this->insurance;
    }

    /**
     * @param ResidentHealthInsurance|null $insurance
     */
    public function setInsurance(?ResidentHealthInsurance $insurance): void
    {
        $this->insurance = $insurance;
    }

    public function getFirstFile()
    {
        return $this->firstFile;
    }

    /**
     * @param null|string $firstFile
     */
    public function setFirstFile(?string $firstFile): void
    {
        $this->firstFile = $firstFile;
    }

    public function getSecondFile()
    {
        return $this->secondFile;
    }

    /**
     * @param null|string $secondFile
     */
    public function setSecondFile(?string $secondFile): void
    {
        $this->secondFile = $secondFile;
    }
}
