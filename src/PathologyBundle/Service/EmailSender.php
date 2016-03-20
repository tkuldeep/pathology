<?php

namespace PathologyBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;

class EmailSender extends ContainerAware
{
    /**
     * Send Email.
     */
    public function sendEmail($email)
    {
        $from = $this->container->getParameter('from_mail');
        $message = \Swift_Message::newInstance()
            ->setSubject($email['subject'])
            ->setFrom($from)
            ->setTo($email['to'])
            ->setBody($email['body'], 'text/html');
        print_r('teena');
        $this->container->get('mailer')->send($message);
    }

}
