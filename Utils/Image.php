<?php

namespace Rad\utils;

use Rad\manager\Config as Config;
use Rad\manager\Log;
use finfo;

/*
 * Copyright (C) 2016 Guillaume Monet
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

final class Image {

    private $tabExt = array('jpg', 'gif', 'png', 'jpeg');    // Extensions autorisees
    private $infosImg = array();
// Variables
    private $extension = '';
    private $message = '';
    private $nomImage = '';

    /**
     * 
     */
    public static function upload() {


	if (!empty($_POST)) {
	    if (!empty($_FILES['fichier']['name'])) {
		$extension = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
		if (in_array(strtolower($extension), $tabExt)) {
		    $infosImg = getimagesize($_FILES['fichier']['tmp_name']);
		    if ($infosImg[2] >= 1 && $infosImg[2] <= 14) {
			if (($infosImg[0] <= WIDTH_MAX) && ($infosImg[1] <= HEIGHT_MAX) && (filesize($_FILES['fichier']['tmp_name']) <= MAX_SIZE)) {
			    if (isset($_FILES['fichier']['error']) && UPLOAD_ERR_OK === $_FILES['fichier']['error']) {
				$nomImage = md5(uniqid()) . '.' . $extension;
				if (move_uploaded_file($_FILES['fichier']['tmp_name'], TARGET . $nomImage)) {
				    $message = 'Upload réussi !';
				} else {
				    $message = 'Problème lors de l\'upload !';
				}
			    } else {
				$message = 'Une erreur interne a empêché l\'uplaod de l\'image';
			    }
			} else {
			    $message = 'Erreur dans les dimensions de l\'image !';
			}
		    } else {
			$message = 'Le fichier à uploader n\'est pas une image !';
		    }
		} else {
		    $message = 'L\'extension du fichier est incorrecte !';
		}
	    } else {
		$message = 'Veuillez remplir le formulaire svp !';
	    }
	}
    }

    public static function createDirectory() {
	if (!is_dir(Config::get("image", "path"))) {
	    if (!mkdir(Config::get("image", "path"), 0755)) {
		Log::error('Erreur : le répertoire cible ne peut-être créé ! Vérifiez que vous diposiez des droits suffisants pour le faire ou créez le manuellement !');
	    }
	}
    }

    public static function getStreamMimeType($buffer) {
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	return $finfo->buffer($buffer);
    }

    public static function getFileMimeType($path) {
	
    }

    /**
     * easy image resize function
     * @param  $file - file name to resize
     * @param  $string - The image data, as a string
     * @param  $width - new image width
     * @param  $height - new image height
     * @param  $proportional - keep image proportional, default is no
     * @param  $output - name of the new file (include path if needed)
     * @param  $delete_original - if true the original image will be deleted
     * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
     * @param  $quality - enter 1-100 (100 is best quality) default is 100
     * @return boolean|resource
     */
    public static function smart_resize_image($file, $string = null, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original = true, $use_linux_commands = false, $quality = 100) {

	if ($height <= 0 && $width <= 0) {
	    return false;
	}
	if ($file === null && $string === null) {
	    return false;
	}

	# Setting defaults and meta
	$info = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
	$image = '';
	$final_width = 0;
	$final_height = 0;
	list($width_old, $height_old) = $info;
	$cropHeight = $cropWidth = 0;

	# Calculating proportionality
	if ($proportional) {
	    if ($width == 0) {
		$factor = $height / $height_old;
	    } elseif ($height == 0) {
		$factor = $width / $width_old;
	    } else {
		$factor = min($width / $width_old, $height / $height_old);
	    }

	    $final_width = round($width_old * $factor);
	    $final_height = round($height_old * $factor);
	} else {
	    $final_width = ( $width <= 0 ) ? $width_old : $width;
	    $final_height = ( $height <= 0 ) ? $height_old : $height;
	    $widthX = $width_old / $width;
	    $heightX = $height_old / $height;

	    $x = min($widthX, $heightX);
	    $cropWidth = ($width_old - $width * $x) / 2;
	    $cropHeight = ($height_old - $height * $x) / 2;
	}

	# Loading image to memory according to type
	switch ($info[2]) {
	    case IMAGETYPE_JPEG: $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);
		break;
	    case IMAGETYPE_GIF: $file !== null ? $image = imagecreatefromgif($file) : $image = imagecreatefromstring($string);
		break;
	    case IMAGETYPE_PNG: $file !== null ? $image = imagecreatefrompng($file) : $image = imagecreatefromstring($string);
		break;
	    default: return false;
	}


	# This is the resizing/resampling/transparency-preserving magic
	$image_resized = imagecreatetruecolor($final_width, $final_height);
	if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) {
	    $transparency = imagecolortransparent($image);
	    $palletsize = imagecolorstotal($image);

	    if ($transparency >= 0 && $transparency < $palletsize) {
		$transparent_color = imagecolorsforindex($image, $transparency);
		$transparency = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
		imagefill($image_resized, 0, 0, $transparency);
		imagecolortransparent($image_resized, $transparency);
	    } elseif ($info[2] == IMAGETYPE_PNG) {
		imagealphablending($image_resized, false);
		$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
		imagefill($image_resized, 0, 0, $color);
		imagesavealpha($image_resized, true);
	    }
	}
	imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);


	# Taking care of original, if needed
	if ($delete_original) {
	    if ($use_linux_commands) {
		exec('rm ' . $file);
	    } else {
		@unlink($file);
	    }
	}

	# Preparing a method of providing result
	switch (strtolower($output)) {
	    case 'browser':
		$mime = image_type_to_mime_type($info[2]);
		header("Content-type: $mime");
		$output = NULL;
		break;
	    case 'file':
		$output = $file;
		break;
	    case 'return':
		return $image_resized;
		break;
	    default:
		break;
	}

	# Writing image according to type to the output destination and image quality
	switch ($info[2]) {
	    case IMAGETYPE_GIF: imagegif($image_resized, $output);
		break;
	    case IMAGETYPE_JPEG: imagejpeg($image_resized, $output, $quality);
		break;
	    case IMAGETYPE_PNG:
		$quality = 9 - (int) ((0.9 * $quality) / 10.0);
		imagepng($image_resized, $output, $quality);
		break;
	    default: return false;
	}

	return true;
    }

}
