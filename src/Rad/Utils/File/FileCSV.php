<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils\File;

/**
 * Description of CSVReader
 *
 * @author Guillaume Monet
 */
class FileCSV extends File {

    /**
     * 
     * @return array
     */
    public function parseCSV(string $separator = ';', bool $hasHeader = false): array {
        if (empty($this->content)) {
            throw new ServerException('Can\'t parse empty content from ' . $this->source);
        }
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $this->content);
        rewind($stream);
        $header = null;
        $data   = [];
        while ($fields = fgetcsv($stream, 0, $separator)) {
            if ($hasHeader && !$header) {
                $header = $fields;
            } else {
                $data[] = array_combine($header ?? range(0, count($fields) - 1), $fields);
            }
        }
        fclose($stream);
        return $data;
    }

}
