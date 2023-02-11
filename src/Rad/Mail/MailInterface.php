<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Mail;

/**
 * Description of MailInterface
 *
 * @author guillaume
 */
interface MailInterface {

    public function createMail(): EmailInterface;
}
