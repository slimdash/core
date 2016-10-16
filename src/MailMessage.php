<?php
namespace SlimDash\Core;

class MailMessage {
	protected $mailer;
	public function __construct(\PHPMailer $mailer) {
		$this->mailer = $mailer;
	}
	public function to($address) {
		$this->mailer->addAddress($address);
	}
	public function subject($subject) {
		$this->mailer->Subject = $subject;
	}
	public function body($body) {
		$this->mailer->Body = $body;
	}
}