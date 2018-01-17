<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Rad\Mail;

/**
 * Description of EmailInterface
 *
 * @author guillaume
 */
interface EmailInterface {

    public function addAttachmentFromData($content, $filename, $contentType = "application/x-unknown-content-type");

    public function addAttachmentFromFile($filename, $forcedFilename = null);

    public function setFrom(string $from, string $alias = null);

    public function addTo(string $to, string $alias = null);

    public function addCC(string $cc, string $alias = null);

    public function addBCC(string $bcc, string $alias = null);

    public function setSubject(string $texte);

    public function setHtml(string $html);

    public function setText(string $text);

    public function setCharset(string $encoding);

    public function setReplyTo(string $replyTo);

    public function send(): bool;
}
