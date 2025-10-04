<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require_once('util4p/CRObject.class.php');
require_once('util4p/Validator.class.php');
require_once('util4p/CRLogger.class.php');
require_once('util4p/AccessController.class.php');
require_once('util4p/Random.class.php');

require_once('Code.class.php');
require_once('config.inc.php');
require_once('init.inc.php');

require __DIR__ .'/PHPMailer/src/Exception.php';
require __DIR__ .'/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function phpmailer_in_hardcode( $subject, $to, $content){

	$mail = new PHPMailer(true);

	try {
		//Server settings
		// $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
		$mail->isSMTP();                                            //Send using SMTP
		$mail->Host       = EMAIL_HOST;                     //Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
		$mail->Username   = EMAIL_FROM;                     //SMTP username
		$mail->Password   = EMAIL_PASSWORD;                               //SMTP password
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
		$mail->Port       = EMAIL_PORT;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

		//Recipients
		$mail->setFrom($mail->Username, 'QuickAuth');		
		$mail->addAddress($to);               //Name is optional
			
		//Content
		$mail->isHTML(true);                                  //Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $content;    

		return $mail->send();
		
	} catch (Exception $e) {
		return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";		
	}

}



/**/
function email_send(CRObject $email)
{
	if (!can_send($email)) {
		$res['errno'] = Code::TOO_FAST;
		return $res;
	}
	$res['errno'] = Code::SUCCESS;

	
	$subject = $email->get('subject');
	$content =  $email->get('content');
	$to = $email->get('email');

	$result = phpmailer_in_hardcode($subject,$to,$content);
	if ($result !== true) {
		
		$res['errno'] = Code::FAIL;
		$res['msg'] = $result;
	}	
	return $res;
}

/* count send stats and reduce spam */
function can_send(CRObject $email)
{
	/* here we only check by username(email) and leave ip check to RateLimiter */
	$rule = new CRObject();
	$rule->set('time_begin', time() - 86400);//last 24 hours
	$rule->set('scope', $email->get('username'));
	$rule->set('tag', 'email.send');
	$res['errno'] = Code::SUCCESS;
	$cnt = CRLogger::getCount($rule);
	return $cnt < MAXIMUM_EMAIL_PER_EMAIL;
}