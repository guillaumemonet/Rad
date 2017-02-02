<?php

namespace Rad\Mail;

final class Mail {

    public static function sendMail($to_email, $subject, $message, $from_email, $from_name) {
        $email = new EMailObject($from_email, $from_name);
        $email->setSubject($subject);
        $email->setText($message);
        $email->send($to_email);
    }

    public static function sendHTMLMail($to_email, $subject, $message, $from_email, $from_name) {
        $email = new EMailObject($from_email, $from_name);
        $email->setSubject($subject);
        $email->setHtml($message);
        $email->send($to_email);
    }

    public static function createMail($from_email, $from_name, $subject = "") {
        return new EMailObject($from_email, $from_name, $subject);
    }

}

?>
