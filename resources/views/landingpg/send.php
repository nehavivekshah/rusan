<?php
require_once 'PHPMailer-master/PHPMailerAutoload.php';

// Proceed with sending the email if reCAPTCHA is verified
$mail = new PHPMailer();
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$admin = "website@creativekey.in";
$mail->isSMTP();
$mail->Host = 'ns1.creativekey.in';
$mail->SMTPAuth = true;
$mail->Username = 'website@creativekey.in';
$mail->Password = '1pfdJd2qhQk6W';
$mail->SMTPSecure = 'ssl';
$mail->IsHTML(true);
$mail->Port = 465;
$mail->From = 'website@creativekey.in';
$mail->FromName = 'New Enquiry';
$mail->AddAddress('iwebbrella@gmail.com', 'Ese Crm');
header('Content-Type: application/json');

$fname = $_POST['name'];
$mailid = $_POST['email'];
$phone = $_POST['phone'];
$services = $_POST['services'];
$message = $_POST['message'];

$formcontent = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Enquiry</title>
</head>
<body>
            <table>
                <tr>
                    <td>Name:</td>
                    <td>' . htmlspecialchars($fname) . '</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>' . htmlspecialchars($mailid) . '</td>
                </tr> 
                <tr>
                    <td>Phone:</td>
                    <td>' . htmlspecialchars($phone) . '</td>
                </tr>
                <tr>
                    <td>Services:</td>
                    <td>' . htmlspecialchars($services) . '</td>
                </tr>
                <tr>
                    <td>Message:</td>
                    <td>' . htmlspecialchars($message) . '</td>
                </tr>
            </table>
</body>
</html>
';

$subject = 'NEW ENQUIRY';
$altbody = 'This is the body in plain text for non-HTML mail clients';
$mail->Subject = $subject;
$mail->Body    = $formcontent;
$mail->AltBody = $altbody;

if (!$mail->Send()) {
    header('Location:thankyou.html');
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
    exit;
} else {
    header('Location:thankyou.html');
}
?>
