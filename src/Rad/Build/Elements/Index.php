<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Build\Elements;

use Rad\Build\Elements\BaseElementTrait;

/**
 * Description of Index
 *
 * @author guillaume
 */
class Index {

    use BaseElementTrait;

    /**
     * 
     * @var string
     */
    public string $name;

    /**
     * 
     * @var Column[]
     */
    public array $columns;
    public $unique = 0;

}
