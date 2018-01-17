<?php

/**
 * @project Rad Framework
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @link https://github.com/guillaumemonet/Rad Git Repository
 */

namespace Rad\Etl;

/**
 * Description of Etl
 *
 * @author guillaume
 */
class Etl {

    private $extractor;
    private $loader;

    public function __construct(EtlExtractor $extractor, EtlLoader $loader) {
        $this->extractor = $extractor;
        $this->loader = $loader;
    }

    /**
     * array(col1_to => array(col1_from=>array(Cleaner::class)
     * ,...)
     * 
     * @param array $mapper
     */
    public function mapTo(array $mapper) {
        $this->extractor->transform($mapper);
    }

    public function save() {
        $this->loader->connect();
        $this->loader->loadDatas($this->extractor->getDatas());
        $this->loader->close();
    }

}
