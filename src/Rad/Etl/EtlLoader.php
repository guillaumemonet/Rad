<?php

/*
 * @project Rad Framework
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @link https://github.com/guillaumemonet/Rad Git Repository
 */

namespace Rad\Etl;

/**
 * Description of EtlLoader
 *
 * @author guillaume
 */
interface EtlLoader {

    public function connect();

    public function close();

    public function loadDatas(array $datas);
}
