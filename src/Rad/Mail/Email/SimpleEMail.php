<?php

/*
 * The MIT License
 *
 * Copyright 2017 Guillaume Monet.
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

namespace Rad\Mail\Email;

/**
 * Description of EMailObject
 *
 * @author Guillaume Monet
 */
class SimpleEMail implements EmailInterface {

    public $to_mail = "";
    public $from_mail = "";
    public $from_name = "";
    public $head = "";
    public $subject = "";
    public $contenu_html = "";
    public $contenu_texte = "";
    public $pj = "";
    public $pj_name = "";
    public $pj_type = "";
    public $array_styles = [];
    public $template = "";
    public $template_name = "";
    public $bcc = "Bcc: \n";

    public function __construct($fromMail, $fromName, $subject = "(sans objet)") {
        $this->from_mail = $fromMail;
        $this->from_name = $fromName;
        $this->subject = $subject;
    }

    public function setText(string $texte) {
        $this->contenu_texte = html_entity_decode($texte);
    }

    public function setHtml(string $html) {
        $this->contenu_html = $html;
    }

    public function setSubject(string $texte) {
        $this->subject = $texte;
    }

    public function addAttachmentFromData($content, $filename, $contentType = "application/x-unknown-content-type") {
        $this->pj_name = $filename;
        $this->pj_type = $contentType;
        $this->pj = $content;
        return $this;
    }

    public function addAttachmentFromFile($filename, $forcedFilename = null) {
        $this->pj_name = $forcedFilename;
        $this->pj = file_get_contents($filename);
        return $this;
    }

    public function loadTemplate($template_name) {
        $tp_smarty = new Smarty;
        $tp_smarty->template_dir = bb::$conf->getConfig()->mail->template->dir . "mails/";
        $tp_smarty->compile_dir = bb::$conf->getConfig()->mail->template->compiled;
        $tp_smarty->config_dir = bb::$conf->getConfig()->mail->template->config;
        $tp_smarty->cache_dir = bb::$conf->getConfig()->mail->template->cache;
        if (bb::$conf->getConfig()->url_rewrite == 1) {
            $tp_smarty->register_outputfilter('filter_template');
        }
        $tp_smarty->language = $_SESSION['langue'];
        $this->template = $tp_smarty;
        $this->template_name = $template_name;
    }

    public function send(): bool {
        $email = "";
        if ($this->template_name != "" && $this->template != "") {
            $this->contenu_html = $this->template->fetch($this->template_name . ".tpl");
        }
        $this->head = "From: " . mb_encode_mimeheader($this->from_name, "UTF-8", "Q") . "<" . $this->from_mail . ">\n";
        $this->head .= $this->bcc;
        if ($reply_to != "") {
            $this->head .= "Reply-To:" . $reply_to . "\n";
        }
        $this->head .= "Date: " . date("D, j M Y G:i:s O") . "\n";
        $this->head .= "MIME-Version: 1.0 \n";
        if (empty($this->contenu_texte)) {
            if (!empty($this->pj)) {
                $this->head .= "Content-Type: multipart/mixed;boundary=MuLtIpArT_BoUnDaRy\n";
                $email .= "--MuLtIpArT_BoUnDaRy\n";
                $email .= "Content-Type: text/html;charset=ISO-8859-1\n";
            } else {
                $this->head .= "Content-Type: text/html;charset=ISO-8859-1\n";
            }
            $email .= $this->contenu_html;
        } else if (empty($this->contenu_html)) {
            if (!empty($this->pj)) {
                $this->head .= "Content-Type: multipart/mixed;boundary=MuLtIpArT_BoUnDaRy\n";
                $email .= "--MuLtIpArT_BoUnDaRy\n";
                $email .= "Content-Type: text/plain;charset=ISO-8859-1\n";
            } else {
                $this->head .= "Content-Type: text/plain;charset=ISO-8859-1\n";
            }
            $email .= $this->contenu_texte;
        } else {
            $this->head .= "Content-Type: multipart/alternative;boundary=MuLtIpArT_BoUnDaRy\n";
            $email .= "--MuLtIpArT_BoUnDaRy\n";
            $email .= "Content-Type: text/plain;charset=ISO-8859-1\n";
            $email .= $this->contenu_texte;
            $email .= "\n\n--MuLtIpArT_BoUnDaRy\n";
            $email .= "Content-Type: text/html;charset=ISO-8859-1\n";
            $email .= $this->contenu_html;
            if (empty($this->pj)) {
                $email .= "\n--MuLtIpArT_BoUnDaRy--";
            }
        }
        if (!empty($this->pj)) {
            $email .= "\n--MuLtIpArT_BoUnDaRy\n";
            $email .= 'Content-Type: ' . $this->pj_type . ';name="' . $this->pj_name . '"' . "\n";
            $email .= 'Content-Transfer-Encoding: base64' . "\n";
            $email .= 'Content-Disposition:attachement;filename="' . $this->pj_name . '"' . "\n\n";
            $email .= chunk_split(base64_encode($this->pj)) . "\n";
            if (!eregi("\n$", $this->pj)) {
                $email .= "\n";
            }
            $email .= "--MuLtIpArT_BoUnDaRy--\n";
        }
        $this->to_mail = $to_mail;

        if (mail($this->to_mail, mb_encode_mimeheader($this->subject, "UTF-8", "Q", ""), $email, $this->head, "-f" . $this->from_mail)) {
            return true;
        } else {
            return false;
        }
    }

    private function buildAttachements(&$email) {
        
    }

    private function buildContent() {
        
    }

    public function addBCC(string $bcc, string $alias = null): \Rad\Mail\EmailInterface {
        
    }

    public function addCC(string $cc, string $alias = null): \Rad\Mail\EmailInterface {
        
    }

    public function addTo(string $to, string $alias = null): \Rad\Mail\EmailInterface {
        
    }

    public function setCharset(string $encoding): \Rad\Mail\EmailInterface {
        
    }

    public function setFrom(string $from, string $alias = null): \Rad\Mail\EmailInterface {
        
    }

    public function setReplyTo(string $replyTo) {
        
    }

}
