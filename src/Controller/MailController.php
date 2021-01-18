<?php
/** @author Anton Ilyasov <hayartur1599@gmail.com> */

namespace App\Controller;


use App\Lib\Mail\EmailSendException;
use App\Lib\Mail\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;


class MailController extends AbstractController {

    private $session;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response
     */
    public function sendPhoto(Request $request, MailerInterface $mailer): Response {
        try {
            $email = $request->get('email');
            if (!isset($email)) {
                return $this->sendErrorResponse('Не указан обязательный параметр: email.');
            }
            /** @var UploadedFile $photo */
            $photo = $request->files->get('photo');
            $file = fopen($photo->getFileInfo(), 'rb');
            if (!$file) {
                return $this->sendErrorResponse('Не удалось получить фото!');
            }

            try {
                $mailService = new MailService($mailer);
                $mailService->sendPhoto($email, $file);
                fclose($file);
            } catch (EmailSendException $e) {
                fclose($file);
                return $this->sendErrorResponse($e->getMessage());
            }
        } catch (\Exception $e) {
            return $this->sendErrorResponse($e->getMessage());
        }

        return new JsonResponse(
                array('error' => false, 'message' => 'Сообщение успешно отправлено!')
        );
    }

    private function sendErrorResponse(string $reason): JsonResponse {
        return new JsonResponse(
                array(
                        'error' => true,
                        'message' => "Произошла ошибка. Причина: ${reason}"
                )
        );
    }

}