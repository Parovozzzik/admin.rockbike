<?php

namespace App\Models;

use App\Models\Entities\EGallery;
use App\Models\Entities\EImage;
use App\Models\Entities\EImageGallery;

/**
 * Class Gallery
 * @package App\Models
 */
class Gallery extends Model
{
    /** @var string */
    public $entityName = EGallery::class;

    /**
     * @param int $imageId
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByImageId(int $imageId): array
    {
        $tableGalleries = self::table();
        $tableImagesGalleries = EImageGallery::table();
        $tableImages = EImage::table();
        $query =
            "SELECT g.gallery_id, g.name, g.slug, g.type, g.visible, g.parent_object_id, g.deleted, g.created_at, " .
                "ig.image_gallery_id " .
            "FROM {$tableGalleries} g " .
            "JOIN {$tableImagesGalleries} ig ON ig.gallery_id = g.gallery_id " .
            "JOIN {$tableImages} i on ig.image_id = i.image_id " .
            "WHERE i.image_id = {$imageId};";
        $result = $this->query($query)->toArray();

        return $result;
    }

    /**
     * @param string $text
     * @return array
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByText(string $text): array
    {
        $table = $this->table();
        $query =
            "SELECT gallery_id, name " .
            "FROM {$table} " .
            "WHERE (name LIKE '%{$text}%' " .
                "OR slug LIKE '%{$text}%') " .
                "AND visible = 1 " .
                "AND deleted = 0;";

        $result = $this->query($query)->toArray('gallery_id', 'name');

        return $result;
    }
}