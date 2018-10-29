<?php

use PHPMailer\PHPMailer\PHPMailer;
require './vendor/autoload.php';

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin:" . $_SERVER['HTTP_ORIGIN']);
header("AMP-Access-Control-Allow-Source-Origin:" . $_SERVER['HTTP_ORIGIN']);
header("Content-type: application/json");
header("access-control-allow-methods:POST, GET, OPTIONS");
header("access-control-allow-headers:Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token");

if (!empty($_POST['email'])) {
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
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->Body = $_POST['text'];
    //Replace the plain text body with one created manually
    $mail->AltBody = $_POST['text'];
    //send the message, check for errors
    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        echo "Message sent!";
        //Section 2: IMAP
        //Uncomment these to save your message in the 'Sent Mail' folder.
        if (save_mail($mail)) {
            echo "Message saved!";
        }
    }
    //Section 2: IMAP
    //IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
    //Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
    //You can use imap_getmailboxes($imapStream, '/imap/ssl') to get a list of available folders or labels, this can
    //be useful if you are trying to get this working on a non-Gmail IMAP server.
    function save_mail($mail)
    {
        //You can change 'Sent Mail' to any other folder or tag
        $path = "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail";
        //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
        $imapStream = imap_open($path, $mail->Username, $mail->Password);
        $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
        imap_close($imapStream);
        return $result;
    }
    $output = ['email' => $_POST['email']];
    echo $post_string = json_encode($output);
    exit;
} else {
    // FAIL
    header("HTTP/1.0 412 Precondition Failed", true, 412);
    $output = ['msg' => 'the filed email is empty'];
    echo $post_string = json_encode($output);
    exit;
}
