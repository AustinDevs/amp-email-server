<?php

use PHPMailer\PHPMailer\PHPMailer;
require './vendor/autoload.php';

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin:" . $_SERVER['HTTP_ORIGIN']);
header("AMP-Access-Control-Allow-Source-Origin:" . $_SERVER['HTTP_ORIGIN']);
header("Content-type: application/json");
header("access-control-allow-methods:POST, GET, OPTIONS");
header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");

if (!empty($_POST['email']) && empty($_POST['_honeypot'])) {
    // SUCCESSFUL
    header("Access-Control-Expose-Headers: AMP-Access-Control-Allow-Source-Origin");
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $mail = new PHPMailer;
    $mail->isSMTP();

    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication - use full email address for gmail
    $mail->Username = getenv('SMTP_USERNAME');
    //Password to use for SMTP authentication
    $mail->Password = getenv('SMTP_PASSWORD');
    //Set who the message is to be sent from
    $mail->setFrom($_POST['email'], $_POST['name']);
    //Set an alternative reply-to address
    $mail->addReplyTo($_POST['email'], $_POST['name']);
    //Set who the message is to be sent to
    $mail->addAddress($_POST['send-to-address'], $_POST['send-to-name']);
    //Set the subject line
    $mail->Subject = $_POST['subject'];
    $body = is_array($_POST['text']) ? join(' ', $_POST['text']) : $_POST['text'];
    $mail->Body = $body;
    //Replace the plain text body with one created manually
    $mail->AltBody = $body;
    //send the message, check for errors
    if (!$mail->send()) {
        header("HTTP/1.0 412 Precondition Failed", true, 412);
        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        echo json_encode("Message Sent");
    }
    exit;
} else {
    echo json_encode("pong");
    exit;
}
