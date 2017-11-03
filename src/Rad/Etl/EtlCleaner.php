<?php

/*
 * @project Rad Framework
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @link https://github.com/guillaumemonet/Rad Git Repository
 */

namespace Rad\Etl;

/**
 * Description of EtlCleaner
 *
 * @author guillaume
 */
abstract class EtlCleaner {

    protected $datas;

    public function __construct($datas) {
        $this->datas = $datas;
    }

    public function clean();
}
