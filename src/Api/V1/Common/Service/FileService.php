<?php
namespace App\Api\V1\Common\Service;

use App\Api\V1\Common\Service\Exception\FileException;
use App\Api\V1\Common\Service\Exception\FileExtensionException;
use App\Api\V1\Common\Service\Exception\FolderNotDefinedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class FileService
 * @package App\Api\V1\Common\Service
 */
class FileService
{
    /**
     * @var int
     */
    private $mask = '%018d';

    /**
     * @var int
     */
    private $pathDelimiterLength = 3;

    /**
     * @var string
     */
    private $cdnPath;

    /**
     * @var array
     */
    private $extensions = [];

    /**
     * FileService constructor.
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        try {
            $this->cdnPath    = $params->get('file_service')['path'];
            $this->extensions = $params->get('file_service')['extensions'];

            if (empty($this->folder)) {
                throw new FolderNotDefinedException();
            }

            $folder = $this->cdnPath . DIRECTORY_SEPARATOR . $this->folder;

            if (!is_dir($folder) || !is_writable($folder)) {
                throw new FolderNotDefinedException();
            }
        } catch (FolderNotDefinedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new FileException();
        }
    }

    /**
     * @param $id
     * @param $base64
     * @return mixed
     */
    public function save($id, $base64)
    {
        if (empty($this->fileName)) {
            throw new FileExtensionException();
        }

        if (empty($this->extension) || !in_array($this->extension, $this->extensions)) {
            throw new FileExtensionException();
        }

        $path = $this->getFilePath($id, $this->fileName, $this->extension);
        $this->base64ToFile($base64, $path);

        return $base64;
    }

    /**
     * @param $id
     * @return bool|string
     */
    public function get($id)
    {
        if (empty($this->fileName)) {
            throw new FileExtensionException();
        }

        if (empty($this->extension) || !in_array($this->extension, $this->extensions)) {
            throw new FileExtensionException();
        }

        $file = $this->getFilePath($id, $this->fileName, $this->extension);

        if (!is_file($file) || !file_exists($file)) {
            return false;
        }

        $imagedata = file_get_contents($file);

        return base64_encode($imagedata);
    }

    /**
     * @param $base64
     * @param $output
     * @return mixed
     */
    private function base64ToFile($base64, $output)
    {
        $base64 = str_replace("-*-", "+", $base64);

        $data = explode( ',', $base64);

        if (empty($data)) {
            $data = $base64;
        } else {
            $data = $data[1];
        }

        $ifp = fopen($output, 'wb');
        fwrite($ifp, base64_decode($data));
        fclose($ifp);

        return $output;
    }

    /**
     * @param $id
     * @return bool|string
     */
    public function remove($id)
    {
        if (empty($this->fileName)) {
            throw new FileExtensionException();
        }

        if (empty($this->extension) || !in_array($this->extension, $this->extensions)) {
            throw new FileExtensionException();
        }

        $file = $this->getFilePath($id, $this->fileName, $this->extension);

        if (!is_file($file) || !file_exists($file)) {
            return false;
        }

        @unlink($file);
    }

    /**
     * Get Path by id
     *
     * @param  int $id
     * @param  string $name
     * @param  string $extension
     * @return string
     */
    private function getFilePath($id, $name, $extension)
    {
        $path = $this->generatePath($id, $name, $extension);

        $filePaths = explode(DIRECTORY_SEPARATOR, $path);
        array_pop($filePaths);

        $dir = $this->cdnPath;
        foreach ($filePaths as $p) {
            $dir .= DIRECTORY_SEPARATOR . $p;

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        return $this->cdnPath . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @param $id
     * @param $name
     * @param string $extension
     * @return string
     */
    private function generatePath($id, $name, $extension)
    {
        $maskId = sprintf($this->mask, $id);
        $path   = sprintf(
            '%s/%s/%s.%s',
            trim($this->folder, DIRECTORY_SEPARATOR),
            implode(DIRECTORY_SEPARATOR, str_split($maskId, $this->pathDelimiterLength)),
            $name,
            $extension
        );

        return $path;
    }
}