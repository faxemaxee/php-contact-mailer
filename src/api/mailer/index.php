<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../../../vendor/autoload.php';
    require '../../utilities/response.php';

    $data = json_decode(file_get_contents('php://input'), true);

    $dotenv = new Dotenv\Dotenv('../../../');
    $dotenv->load();

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = getenv('SMTP_DEBUG_LVL');
        $mail->isSMTP();
        $mail->Host = getenv('MAIL_SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MAIL_SMTP_USER');
        $mail->Password = getenv('MAIL_SMTP_PASS');
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom(getenv('SENDER_EMAIL'), getenv('SENDER_NAME'));
        $mail->addAddress(getenv('RECIEVER_EMAIL'), getenv('RECIEVER_NAME'));
        // $mail->addReplyTo('info@example.com', 'Information');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Here is the subject';
        $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        response(200, 'Message has been sent.', $_POST);
    } catch (Exception $e) {
        response(500, 'Message could not be sent.', $mail->ErrorInfo);
    }