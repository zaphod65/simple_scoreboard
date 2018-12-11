<?php

namespace Service\Email;

/**
 * This class has barely any implementation to provide a consistent interface to
 * other classes that wish to send email; the implementation below (the most naive)
 * can be easily switched out for another one that uses more full-features like PHPMail.
 * I've tried to keep the current implementation as PHP-native as possible though,
 * so this only uses simple PHP functions in the standard library.
 */
class EmailService {
    private $fromAddress = 'noreply@samplemail.com'; // this needs to have some default set
    public function send($address, $subject, $body) {
        return mail($address, $subject, $body, $this->fromAddress); // Need to set the from address here
    }
}
