<?php
/** @author Anton Ilyasov <hayartur1599@gmail.com> */

namespace App\Lib\Mail;


use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


class MailService {

    private const EMAIL_FROM = 'post.service34@yandex.ru';
    /** @var MailerInterface $mailer */
    private $mailer;

    public function __construct(MailerInterface $mailer) {
        $this->mailer = $mailer;
    }

    /**
     * @param string $email
     * @param resource $file
     * @throws EmailSendException
     */
    public function sendPhoto(string $email, $file): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new EmailSendException('Некорректный email.');
        }

        $message = new Email();
        $message->addFrom(self::EMAIL_FROM);
        $message->addTo($email);
        $message->subject('Камера определила объект');
        $message->html(
                '<p>Добрый день! Ваша камера на Raspberry PI определила один из объектов! Фото прилагается.</p>'
        );
        $message->attach($file, 'detection_photo.jpg');

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            throw new EmailSendException(
                    "Не удалось отправить письмо. Причина: {$e->getMessage()}",
                    0,
                    $e
            );
        }
    }

}