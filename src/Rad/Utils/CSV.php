<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils;

/**
 * Description of CSVReader
 *
 * @author Guillaume Monet
 */
class CSV {

    private $filename = null;
    private $hasHeader = null;
    private $separator = ";";

    public function __construct($filename, $separator = ";", $hasHeader = false) {
        $this->filename = $filename;
        $this->hasHeader = $hasHeader;
        $this->separator = $separator;
    }

    /**
     * 
     * @return array
     */
    public function read(): array {
        $all_rows = [];
        $header = null;
        $file = fopen($this->filename, 'r+');
        while ($row = fgetcsv($file)) {
            if ($header === null) {
                $header = $row;
                continue;
            }
            $all_rows[] = array_combine($header, $row);
        }
        fclose($file);
        return $all_rows;
    }

}
