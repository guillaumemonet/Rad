<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils\File;

/**
 * Description of File
 *
 * @author Guillaume Monet
 */
class File {

    /**
     * 
     * @var string
     */
    public $source;

    /**
     * 
     * @var string
     */
    public $content;

    public function __construct(string $source = null) {
        $this->source = $source;
    }

    public function load(string $source = null): File {
        if ($source == null) {
            $source = $this->source;
        }
        $this->content = file_get_contents($source);
        return $this;
    }

    public function save(string $destination = null): File {
        if ($destination == null) {
            $destination = $this->source;
        }
        file_put_contents($destination, $this->content);
        return $this;
    }

    public function delete(): File {
        unlink($this->source);
        $this->source = null;
        return $this;
    }

    public function moveTo(string $destination): File {
        $this->load();
        $this->delete();
        $this->source = $destination;
        $this->save();
        return $this;
    }

    public function copyTo(string $destination): File {
        $this->load();
        $copy         = clone $this;
        $copy->source = $destination;
        $copy->save();
        return $copy;
    }

   

}
