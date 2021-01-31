<?php

namespace App\Models;

use App\Models\Entities\EGallery;

/**
 * Class Gallery
 * @package App\Models
 */
class Gallery extends Model
{
    /** @var string */
    public $entityName = EGallery::class;
}