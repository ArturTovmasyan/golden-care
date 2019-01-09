<?php
namespace App\Api\V1\Common\Service\Helper;

use App\Api\V1\Common\Service\FileService;
/**
 * Class FileService
 * @package App\Api\V1\Common\Service
 */
class UserAvatarHelper extends FileService
{
    /**
     * @var string
     */
    public $folder = 'user-avatar';

    /**
     * @var string
     */
    public $fileName = 'avatar';

    /**
     * @var string
     */
    public $extension = '';
}
