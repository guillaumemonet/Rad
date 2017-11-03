<?php

/*
 * @project Rad Framework
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @link https://github.com/guillaumemonet/Rad Git Repository
 */

namespace Rad\Etl;

/**
 * Description of StdOut_EtlExtractor
 *
 * @author guillaume
 */
class StdOut_EtlExtractor implements EtlLoader{
    
    public function close() {
        
    }

    public function connect() {
        
    }

    public function loadDatas(array $datas) {
        error_log(print_r($datas));
    }

}
