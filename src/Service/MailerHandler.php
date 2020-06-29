<?php


namespace App\Service;


use App\Entity\Contact;
use Twig\Environment;
use Twig\Template;
use Twig\Token;

class MailerHandler
{
    private $mailer;
    private $twig;

    public function __construct(\Swift_Mailer $mailer, Environment $twig)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    public function sendMailContact(Contact $contact){
        $message = (new \Swift_Message('Nouveau message sur '.$contact->getProduct()))
            ->setFrom($contact->getEmail())
            ->setTo("test@email.com")
            ->setBody(
                $this->twig->render("email/contact.html.twig",[
                    'contact'=> $contact
                ]) ,
                'text/html'
            );
//
       $this->mailer->send($message);
    }

}
