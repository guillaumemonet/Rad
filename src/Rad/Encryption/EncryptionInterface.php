<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Encryption;

/**
 * Description of EncryptionInterface
 *
 * @author guillaume
 */
interface EncryptionInterface {

    public function crypt(string $data);

    public function decrypt(string $data);

    public function sign(string $data, string $secret);
}
