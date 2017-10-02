<?php

namespace Rad\Mail;

class Email extends Swift_Message {
    protected $template = null;
    protected $htmlPart = "";
    protected $textPart = "";

    public function __construct($subject = null, $body = null, $contentType = null, $charset = null) {

        parent::__construct($subject, $body, $contentType, $charset);

        $this->setCharset('ISO-8859-15');
        $this->template = new Smarty;
        $this->template->template_dir = bb::$conf->getConfig()->mail->template->path;
        $this->template->compile_dir = bb::$conf->getConfig()->mail->template->compiled;
        $this->template->config_dir = bb::$conf->getConfig()->mail->template->config;
        $this->template->cache_dir = bb::$conf->getConfig()->mail->template->cache;

        $this->template->assign("subject", $subject);
        $this->template->assign("root_url", bb::$conf->getConfig()->root_url);
        $this->template->assign("media_url", bb::$conf->getConfig()->media_url);

        $this->template->assign("dateFormatter", new IntlDateFormatter(
                'fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE
        ));

        if (!isset($_SESSION["univers_title"])) {
            $univers = new Univers();
            $_SESSION["univers_title"] = $univers->titre;
            $_SESSION["univers_url"] = $univers->domaine;
            $_SESSION["univers_name"] = $univers->nom_court_univers;
        }

        $this->template->assign("univers_title", $_SESSION['univers_title']);
        $this->template->assign("univers_url", $_SESSION['univers_url']);
        $this->template->assign("univers_name", $_SESSION['univers_name']);

        if (Boutique::getHandle()->client->id_client !== 0) {
            $this->template->assign("prenom_client", utf8_encode(Boutique::getHandle()->client->prenom));
            $this->template->assign("nom_client", utf8_encode(Boutique::getHandle()->client->nom));
        }

        if (isset($_SESSION['langue'])) {
            $this->template->language = $_SESSION['langue'];
        }
    }

    function addAttachmentFromData($content, $filename, $content_type = "application/x-unknown-content-type") {
        $attachment = new Swift_Attachment($content, $filename, $content_type);
        return $this->attach($attachment);
    }

    function addAttachmentFromFile($filename, $forcedFilename = null) {
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

        return $this->setHtml(
                        $isUtf8 ? iconv('UTF-8', $this->getCharset() . "//IGNORE", $this->getTemplate()->fetch($templateName)) : $this->getTemplate()->fetch($templateName), $autoGenerateTxt);
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

                    $html2Txt = new \Html2Text\Html2Text($mock->saveHTML());

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

?>
