<?php

/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Rad\Utils;

use finfo;
use GdImage;
use Rad\Error\ServiceException;
use Rad\Log\Log;

class Image {

    /**
     * 
     * @var GdImage
     */
    public $image;

    /**
     * 
     * @var int
     */
    public $height;

    /**
     * 
     * @var int
     */
    public $width;

    /**
     * 
     * @var int
     */
    public $type;

    /**
     * 
     * @var int
     */
    public $quality = -1;

    /**
     * 
     * @var array
     */
    public $image_functions = [
        'create' => [
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG  => 'imagecreatefrompng',
            IMAGETYPE_GIF  => 'imagecreatefromgif',
            IMAGETYPE_WEBP => 'imagecreatefromwebp'
        ],
        'save'   => [
            IMAGETYPE_JPEG => 'imagejpeg',
            IMAGETYPE_PNG  => 'imagepng',
            IMAGETYPE_GIF  => 'imagegif',
            IMAGETYPE_WEBP => 'imagewebp',
        ]
    ];

    public function __construct($source = null) {
        if ($source == null) {
            return;
        } else {
            $this->load($source);
        }
    }

    /**
     * 
     * @param String $source
     */
    public function load($source) {
        $infos_image = @getimagesize($source);
        if (!$infos_image) {
            Log::getHandler()->error('Unable to get image size for ' . $source);
            return;
        }
        list($width, $height, $type) = $infos_image;
        $this->width  = $width;
        $this->height = $height;
        $this->type   = $type;

        if (!array_key_exists($type, $this->image_functions['create'])) {
            throw new ServiceException('Unsupported image type');
        }
        $create_function = $this->image_functions['create'][$type];
        $this->image     = $create_function($source);
    }

    /**
     * 
     * @param String $destination
     */
    public function save($destination) {
        if (!array_key_exists($this->type, $this->image_functions['save'])) {
            throw new ServiceException('Unsupported image type');
        }
        $save_function = $this->image_functions['save'][$this->type];
        if ($this->type === IMAGETYPE_JPEG || $this->type === IMAGETYPE_WEBP) {
            $success = $save_function($this->image, $destination, $this->quality);
        } else {
            $success = $save_function($this->image, $destination);
        }
        if (!$success) {
            Log::getHandler()->error('Unable to save ' . $destination);
        }
    }

    /**
     * 
     * @param int $type
     */
    public function convertTo($type = IMAGETYPE_WEBP) {
        $this->type = $type;
    }

    public static function getStreamMimeType($buffer) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($buffer);
    }

    public static function convertToWebP($source, $destination, $quality = 80) {
        $image          = new Image($source);
        $image->quality = $quality;

        $image->convertTo(IMAGETYPE_WEBP);
        $image->save($destination);
        // Libérer la mémoire
        $image = null;
    }

    /**
     * 
     * @param String $source
     * @param String $destination
     * @param int $height
     * @return Image
     */
    public static function resize($source, $destination, $height): Image {
        $image            = new Image($source);
        $newimage         = new Image();
        $newimage->height = $height;
        $ratio            = $newimage->height / $image->height;
        $newimage->width  = round($image->width * $ratio);

        // Création d'une nouvelle image avec les dimensions souhaitées

        $newimage->image = imagecreatetruecolor($width, $height);

        // Redimensionnement de l'image
        imagecopyresampled($newimage->image, $image->image, 0, 0, 0, 0, $newimage->width, $newimage->height, $image->width, $image->height);
        $newimage->save($destination);
        // Libération de la mémoire
        $image = null;
        return $newimage;
    }

}
