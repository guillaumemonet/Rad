<?php

namespace Rad\Mail;

use DOMDocument;
use Html2Text\Html2Text;
use IntlDateFormatter;
use Rad\Template\Template;
use Swift_Attachment;
use Swift_Message;

class Email extends Swift_Message implements EmailInterface {

    protected $template = null;
    protected $htmlPart = "";
    protected $textPart = "";

    public function __construct($subject = null, $body = null, $contentType = null, $charset = null) {
        parent::__construct($subject, $body, $contentType, $charset);
        $this->setCharset('ISO-8859-15');
        $this->template = clone Template::getHandler();
        $this->template->assign("subject", $subject);
        $this->template->assign("dateFormatter", new IntlDateFormatter(
                'fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE
        ));
    }

    public function addAttachmentFromData($content, $filename, $content_type = "application/x-unknown-content-type") {
        $attachment = new Swift_Attachment($content, $filename, $content_type);
        return $this->attach($attachment);
    }

    public function addAttachmentFromFile($filename, $forcedFilename = null) {
        $attachment = Swift_Attachment::fromPath($filename);
        if ($forcedFilename !== null) {
            $attachment->setFilename($forcedFilename);
        }
        return $this->attach($attachment);
    }

    public function getTemplate() {
        return $this->template;
    }

    /**
     * Compile the loaded template
     */
    public function compileTemplate($templateName, $autoGenerateTxt = true, $isUtf8 = true) {
        return $this->setHtml($isUtf8 ? iconv('UTF-8', $this->getCharset() . "//IGNORE", $this->getTemplate()->fetch($templateName)) : $this->getTemplate()->fetch($templateName), $autoGenerateTxt);
    }

    public function setHtml($html, $autoGenerateTxt = true) {
        $this->setBody($html, 'text/html');
        if ($autoGenerateTxt) {
            // Retrieve body from compiled html
            $document = new DOMDocument();
            if (@$document->loadHTML($html)) {
                // Retrieve body content from html
                if (null !== ($body = $document->getElementsByTagName('body')->item(0))) {
                    // extract body content and reinject it in the mock
                    $mock = new DOMDocument();
                    foreach ($body->childNodes as $child) {
                        $mock->appendChild($mock->importNode($child, true));
                    }
                    $html2Txt = new Html2Text($mock->saveHTML());
                    $this->setText($html2Txt->getText());
                }
            }
        }
        return $this;
    }

    public function setText($text) {
        return $this->addPart($text, 'text/plain');
    }

}
