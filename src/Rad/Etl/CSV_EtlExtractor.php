<?php

/*
 * @project Rad Framework
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @link https://github.com/guillaumemonet/Rad Git Repository
 */

namespace Rad\Etl;

use Rad\Utils\CSV;

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
        $csv = new CSV($params["filename"],$params["separator"],$params["hasHeader"]);
        $datas = $csv->read();
        $this->headers = array_keys($datas);
        $this->datas = $datas;
    }

    public function getDatas(): array {
        
    }

    public function transform(array $mapper) {
        $this->transformed_datas = array();
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
