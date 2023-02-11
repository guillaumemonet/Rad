<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Mail;

/**
 * Description of EmailInterface
 *
 * @author guillaume
 */
interface EmailInterface {

    public function addAttachmentFromData($content, $filename, $contentType = "application/x-unknown-content-type"): self;

    public function addAttachmentFromFile($filename, $forcedFilename = null): self;

    public function setFrom(string $from, string $alias = null): self;

    public function addTo(string $to, string $alias = null): self;

    public function addCC(string $cc, string $alias = null): self;

    public function addBCC(string $bcc, string $alias = null): self;

    public function setReplyTo(string $replyTo, string $alias): self;

    public function setSubject(string $texte): self;

    public function setHtml(string $html): self;

    public function setText(string $text): self;

    public function setCharset(string $encoding): self;

    public function send(): bool;
}
