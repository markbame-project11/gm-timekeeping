<?php
/**
 * Sends text email.
 *
 * send_email(new PHPMailer(), 'Subject', 'body', 'antonio.cruda@gumi.ph');
 */
function send_email($mail, $subject, $body, $email)
{
	$mail->IsSMTP(); // enable SMTP
	// $mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
	$mail->SMTPAuth = true; // authentication enabled
	$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
	$mail->Host = "smtp.gmail.com";
	$mail->Port = 465; // or 587
	$mail->IsHTML(true);
	$mail->Username = "tks@gumi.ph ";
	$mail->Password = "payrollgumi2014";
	$mail->SetFrom("tks@gumi.ph ");
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AddAddress($email);

	return $mail->Send();
}