<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils;

use finfo;
use GdImage;
use Rad\Error\ServiceException;
use Rad\Log\Log;

class Image {

    /**
     * 
     * @var string
     */
    public $source;

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
        'build'  => [
            IMAGETYPE_JPEG => 'imagejpeg',
            IMAGETYPE_PNG  => 'imagepng',
            IMAGETYPE_GIF  => 'imagegif',
            IMAGETYPE_WEBP => 'imagewebp',
        ]
    ];

    public function __construct($source = null) {
        $this->source = $source;
    }

    /**
     * 
     * @param String $source
     */
    public function load($source = null) {
        if ($source == null) {
            $source = $this->source;
        }

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
        try {
            $create_function = $this->image_functions['create'][$type];
            $this->image     = $create_function($source);
        } catch (Exception $ex) {
            Log::getHandler()->error('Unable to load image from ' . $source);
        }
        return $this;
    }

    /**
     * 
     * @return bool
     */
    public function exists(): bool {
        return file_exists($this->source);
    }

    /**
     * 
     * @param String $destination
     */
    public function save($destination) {
        if (!array_key_exists($this->type, $this->image_functions['build'])) {
            throw new ServiceException('Unsupported image type');
        }
        $save_function = $this->image_functions['build'][$this->type];
        if ($this->type === IMAGETYPE_JPEG || $this->type === IMAGETYPE_WEBP) {
            $success = $save_function($this->image, $destination, $this->quality);
        } else {
            $success = $save_function($this->image, $destination);
        }
        if (!$success) {
            Log::getHandler()->error('Unable to save image to ' . $destination);
        }
        return $this;
    }

    public function display($raw = true) {
        if ($raw) {
            $extension = pathinfo($this->source, PATHINFO_EXTENSION);
            $mime_type = 'image/' . $extension;
            $content   = file_get_contents($this->source);
        } else {
            if (!array_key_exists($this->type, $this->image_functions['build'])) {
                throw new ServiceException('Unsupported image type');
            }
            $display_function = $this->image_functions['build'][$this->type];
            $extension        = image_type_to_extension($this->type);
            $mime_type        = 'image/' . $extension;
            ob_start();
            $success          = $display_function($this->image);
            $content          = ob_get_clean();
            if (!$success) {
                throw new ServiceException('Unable to display image');
            }
        }
        header('Content-Type: ' . $mime_type);
        echo $content;
    }

    /**
     * 
     * @param int $type
     */
    public function convertTo($type = IMAGETYPE_WEBP) {
        $this->type = $type;
        return $this;
    }

    public static function getStreamMimeType($buffer) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($buffer);
    }

    public static function convertToWebP($source, $destination, $quality = 80) {
        $image          = new Image($source);
        $image->quality = $quality;
        $image->convertTo(IMAGETYPE_WEBP)->save($destination);
        return $image;
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
        $image->load();
        $newimage         = new Image();
        $newimage->height = $height;
        $ratio            = $newimage->height / $image->height;
        $newimage->width  = round($image->width * $ratio);

        // Création d'une nouvelle image avec les dimensions souhaitées

        $newimage->image = imagecreatetruecolor($newimage->width, $newimage->height);

        // Redimensionnement de l'image
        imagecopyresampled($newimage->image, $image->image, 0, 0, 0, 0, $newimage->width, $newimage->height, $image->width, $image->height);
        $newimage->save($destination);
        // Libération de la mémoire
        $image = null;
        return $newimage;
    }

}
