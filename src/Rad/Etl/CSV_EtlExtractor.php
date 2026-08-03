<?php

/*
 * @project Rad Framework
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @link https://github.com/guillaumemonet/Rad Git Repository
 */

namespace Rad\Etl;

use Rad\Utils\File\FileCSV;

/**
 * Description of CSV_EtlExtractor
 *
 * @author guillaume
 */
class CSV_EtlExtractor implements EtlExtractor {
    private $headers;
    private $datas;
    private $transformed_datas;

    public function close() {

    }

    public function connect(array $params) {
        $csv = new FileCSV($params['filename']);
        $csv->load();
        $datas         = $csv->parseCSV($params['separator'] ?? ';', $params['hasHeader'] ?? false);
        $this->headers = !empty($datas) ? array_keys($datas[0]) : [];
        $this->datas   = $datas;
    }

    public function getDatas(): array {
        return $this->datas ?? [];
    }

    public function transform(array $mapper) {
        $this->transformed_datas = [];
        foreach ($mapper as $key => $values) {
            foreach ($values as $tab_head => $cleaners) {
                $this->transformed_datas[$key] .= $this->datas[$tab_head];
            }
        }
    }

    public function getHeaders(): array {
        return $this->headers;
    }

}
