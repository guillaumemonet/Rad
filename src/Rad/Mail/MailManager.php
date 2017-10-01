<?php

namespace Rad\Mail;

use Swift_Mailer;
use Swift_SendmailTransport;

final class MailManager extends Swift_Mailer implements IManager {

    protected $transport = null;

    public function __construct(/* Swift_Transport $transport */) {

        parent::__construct(new Swift_SendmailTransport());
    }

    public function createMail($subject = "") {
        return new EMail($subject);
    }

}

?>
