<?php

/*
 * @project Rad Framework
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @link https://github.com/guillaumemonet/Rad Git Repository
 */

namespace Rad\Etl;

/**
 * Description of EtlExtractor
 *
 * @author guillaume
 */
interface EtlExtractor {

    public function connect(array $params);

    public function transform();

    public function close();

    public function getDatas(): array;

    public function getHeaders(): array;
}
