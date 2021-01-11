<?php

namespace App\Services;

use App\Settings\Exceptions\StorageException;
use Intervention\Image\ImageManager;

/**
 * Class ImagesService
 * @package App\Services
 */
class ImagesService
{
    /** @var string */
    CONST STORAGE_PATH = '.' . DS . 'App' . DS . 'Storage' . DS . 'images';
    /** @var array */
    CONST SIZES = [
        'xs' => [
            'width' => 150,
            'height' => 150,
        ],
        's' => [
            'width' => 250,
            'height' => 170,
        ],
        'm' => [
            'width' => 480,
            'height' => 700,
        ],
        'l' => [
            'width' => 650,
            'height' => 750,
        ]
    ];

    /** @var ImageManager */
    protected $manager;

    /**
     * ImagesService constructor.
     */
    public function __construct()
    {
        $this->manager = new ImageManager(['driver' => 'imagick']);
    }


    public function checkSizes(string $path): bool
    {
        $sizes = $this->getSizes($path);

        /*
         * TODO: need to add calculating from type of galleries
         */

        return true;
    }

    /**
     * @param string $path
     * @return int|null
     */
    public function getSize(string $path): ?int
    {
        $image = $this->manager->make($path);
        $result = $image->filesize();

        return $result !== false ? $result : null;
    }

    /**
     * @param array $file
     * @param \DateTime $dateTime
     * @return string|null
     */
    public function upload(array $file, \DateTime $dateTime): ?string
    {
        $folderPath = self::STORAGE_PATH . DS . $this->getPathFromDateTime($dateTime) . DS;
        $fileName = '';
        try {
            $manager = new ImageManager(['driver' => 'imagick']);
            $image = $manager->make($file['tmp_name'][0]);
            $fileName = md5($image->basename . microtime()) . '.' . self::getExtension($image->mime);

            foreach (self::SIZES as $key => $sizes) {
                $newImage = clone $image;
                $fileFullPath = $folderPath . $key;
                $folderExists = $this->createFolders($fileFullPath);
                if ($folderExists === false) {
                    throw new StorageException('Folder does not exists.', 404);
                }
                $newImage = $newImage->fit($sizes['width'], $sizes['height']);
                $newImage->save($fileFullPath . DS . $fileName);
            }
        } catch (\Exception $e) {
            if ($fileName !== '') {
                $this->unlink($fileName, $dateTime);
            }
            return null;
        }

        return $fileName;
    }

    /**
     * @param string $fileName
     * @param \DateTime $dateTime
     * @return bool
     */
    public function unlink(string $fileName, \DateTime $dateTime): bool
    {
        $folderPath = self::STORAGE_PATH . DS . $this->getPathFromDateTime($dateTime) . DS;

        $result = false;
        foreach (self::SIZES as $key => $sizes) {
            $fileFullPath = $folderPath . $key . DS . $fileName;
            $result = unlink($fileFullPath);
        }

        return $result;
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function createFolders(string $path): bool
    {
        $pathArray = explode('/', $path);

        $path = '.';
        foreach ($pathArray as $item) {
            $path .= DS . $item;
            if (is_dir($path) === false) {
                mkdir($path);
            }
        }

        return is_dir($path);
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    protected function getPathFromDateTime(\DateTime $dateTime): string
    {
        return $dateTime->format('Y/m/d');
    }

    /**
     * @param $mimeType
     * @return mixed
     */
    public static function getExtension($mimeType)
    {
        $extensions = [
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
        ];

        return $extensions[$mimeType];
    }

    /**
     * @param string $path
     * @return array
     */
    public function getSizes(string $path): array
    {
        $image = $this->manager->make($path);

        $result = [
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
        ];

        return $result;
    }
}