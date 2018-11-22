<?php
namespace App\Api\V1\Common\Service\Helper;

use App\Api\V1\Common\Service\FileService;
/**
 * Class FileService
 * @package App\Api\V1\Common\Service
 */
class ResidentPhotoHelper extends FileService
{
    /**
     * @var string
     */
    public $folder = 'resident-photo';

    /**
     * @var string
     */
    public $fileName = 'photo';

    /**
     * @var string
     */
    public $extension = 'jpeg';
}