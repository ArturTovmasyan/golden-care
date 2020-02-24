<?php

namespace App\Api\V1\Common\Service;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

/**
 * Class S3Service
 * @package App\Api\V1\Common\Service
 */
class S3Service
{
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
     * @param $base64Data
     * @param $s3Id
     * @param $fileType
     * @param $mimeType
     */
    public function uploadFile($base64Data, $s3Id, $fileType, $mimeType): void
    {
        try {
            $explodeData = explode(',', $base64Data);
            $decodedData = base64_decode(end($explodeData));

            $this->getS3Client()->putObject([
                'Bucket' => getenv('AWS_BUCKET'),
                'Key' => $fileType . '/' . $s3Id,
                'Body' => $decodedData,
                'ContentType' => $mimeType,
                'ACL' => 'public-read'
            ]);

        } catch (S3Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $s3Id
     * @param $fileType
     * @return Result
     */
    public function downloadFile($s3Id, $fileType): Result
    {
        $result = $this->getS3Client()->getObject(array(
            'Bucket' => getenv('AWS_BUCKET'),
            'Key' => $fileType . '/' . $s3Id,
        ));

        return $result;
    }

    /**
     * @param $s3Id
     * @param $fileType
     * @return Result
     */
    public function removeFile($s3Id, $fileType): Result
    {
        $result = $this->getS3Client()->deleteObject(array(
            'Bucket' => getenv('AWS_BUCKET'),
            'Key' => $fileType . '/' . $s3Id,
        ));

        return $result;
    }
}
