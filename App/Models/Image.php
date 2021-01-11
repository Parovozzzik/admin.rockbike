<?php

namespace App\Models;

use App\Models\Entities\EImage;

/**
 * Class Image
 * @package App\Models
 */
class Image extends Model
{
    /** @var string */
    public $entityName = EImage::class;
}