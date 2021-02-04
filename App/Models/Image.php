<?php

namespace App\Models;

use App\Models\Entities\EGallery;
use App\Models\Entities\EImage;
use App\Models\Entities\EImageGallery;

/**
 * Class Image
 * @package App\Models
 */
class Image extends Model
{
    /** @var string */
    public $entityName = EImage::class;

    /**
     * @param int $galleryId
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByGalleryId(int $galleryId): array
    {
        $tableGalleries = EGallery::table();
        $tableImagesGalleries = EImageGallery::table();
        $tableImages = self::table();
        $query =
            "SELECT i.image_id, i.path, i.description, i.visible, i.deleted, i.created_at, ig.image_gallery_id " .
            "FROM {$tableImages} i " .
            "JOIN {$tableImagesGalleries} ig ON ig.image_id = i.image_id " .
            "JOIN {$tableGalleries} g on ig.gallery_id = g.gallery_id " .
            "WHERE g.gallery_id = {$galleryId};";
        $result = $this->query($query)->toArray();

        return $result;
    }
}