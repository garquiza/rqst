<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('../vendor/autoload.php');

$mail = new PHPMailer;

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'emailgroup658@gmail.com';
$mail->Password = 'hwnh niug wbwh sdky';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->isHTML(true);
$mail->setFrom('emailgroup658@gmail.com', 'Request System');
