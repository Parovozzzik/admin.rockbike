<?php

namespace App\Models;

use App\Models\Entities\EGallery;
use App\Models\Entities\EImage;
use App\Models\Entities\EImageGallery;

/**
 * Class ImageGallery
 * @package App\Models
 */
class ImageGallery extends Model
{
    /** @var string */
    public $entityName = EImageGallery::class;

    /**
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return array
     * @throws \Exception
     */
    public function getList(int $page = 0, int $limit = 25, array $filters = []): array
    {
        $tableImages = EImage::table();
        $tableGalleries = EGallery::table();
        $tableImagesGalleries = $this->table();

        $queryCount =
            "SELECT COUNT(image_gallery_id) as count " .
            "FROM {$tableImagesGalleries};";
        $count = $this->query($queryCount)->toArray('count')[0];

        $where = [];
        if (isset($filters['type'])) {
            $where[] = " gl.type = '{$filters['type']}' ";
        }
        if (isset($filters['good_id'])) {
            $where[] = " g.good_id = {$filters['good_id']} ";
        }
        $query =
            "SELECT i.image_id, i.path, i.created_at, ig.image_gallery_id, ig.is_main, ig.priority, " .
                "gl.gallery_id, gl.name as gallery_name " .
            "FROM {$tableImagesGalleries} ig " .
            "JOIN {$tableGalleries} gl ON gl.gallery_id = ig.gallery_id " .
            "JOIN {$tableImages} i ON i.image_id = ig.image_id " .
            ((count($where) > 0) ? ("WHERE " . implode(" AND ", $where) . ";") : ";");
        $data = $this->query($query);
        $pager = Model::buildPager($page, $count, $limit);

        return [
            'data' => $data->toArray(),
            'pager' => $pager
        ];
    }

    /**
     * @param int $imageGalleryId
     * @return EImageGallery|null
     * @throws \App\Settings\Exceptions\DatabaseException
     */
    public function getByIdWithRelations(int $imageGalleryId): ?EImageGallery
    {
        $tableImages = EImage::table();
        $tableGalleries = EGallery::table();
        $tableImagesGalleries = $this->table();

        $query =
            "SELECT ig.*, i.path, i.description, i.created_at, g.name as gallery_name " .
            "FROM {$tableImagesGalleries} ig " .
            "JOIN {$tableGalleries} g ON g.gallery_id = ig.gallery_id " .
            "JOIN {$tableImages} i ON i.image_id = ig.image_id " .
            "WHERE ig.image_gallery_id = {$imageGalleryId};";
        /** @var EImageGallery $imageGallery */
        $imageGallery = $this->query($query)->first();

        return $imageGallery !== false ? $imageGallery : null;
    }
}