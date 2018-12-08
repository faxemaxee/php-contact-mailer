<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../../include/phpmailer/Exception.php';
    require '../../include/phpmailer/PHPMailer.php';

    require_once '../../utilities/load-env.php';
    require_once '../../utilities/response.php';
    require_once '../../utilities/template-builder.php';
    require_once '../../utilities/transpiler.php';

    $data = json_decode(file_get_contents('php://input'), true);

    /* CHECK FAKE INPUT */
    $fakeInputs = explode(',', getenv('FAKE_KEYS'));
    foreach ($fakeInputs as $value) {
        if(isset($data[$value]) && strlen($data[$value]) > 0) {
            response(200, 'Message has been sent.');
            die();
        }
    }

    foreach ($required_keys as $value) {
        if ( !isset($data[$value]) || strlen($data[$value]) <= 2 ) {
            response(400, $label_keys[$value] . ' ist erforderlich bzw. zu kurz.');
            die();
        }
    }

    /* OPTIN START */
    if (!isset($data[getenv('OPT_IN_KEY')])) {
        response(400, 'Bitte akzeptiere die Datenschutzhinweise sowie die allgemeinen Geschäftsbedingungen.');
        die();
    }
    /* OPTIN END */

    $email = build(getenv('TEMPLATE_NAME'), $data);
    
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
        $mail->Subject = 'Neue Nachricht von deiner Webseite';
        $mail->Body    = $email['html'];
        $mail->AltBody = $email['text'];

        $mail->send();
        response(200, 'Message has been sent.', $_POST);
    } catch (Exception $e) {
        response(500, 'Message could not be sent.', $mail->ErrorInfo);
    }