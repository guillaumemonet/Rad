<?php

namespace Rad\Mail;

use Swift_Mailer;
use Swift_SendmailTransport;

class Swift_MailHandler extends Swift_Mailer implements MailInterface {

    protected $transport = null;

    public function __construct() {
        parent::__construct(new Swift_SendmailTransport());
    }

    public function createMail(string $subject = ""): EmailInterface {
        return new EMail($subject);
    }

}
