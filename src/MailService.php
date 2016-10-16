<?php
namespace SlimDash\Core;

class MailService {
	protected $mailer, $engine;
	public function __construct(\PHPMailer $mailer, $viewEngine) {
		$this->mailer = $mailer;
		$this->engine = $viewEngine;
	}
	public function send($template, $data, $callback) {
		$message = new MailMessage($this->mailer);
		$template = $this->engine->loadTemplate($template);
		$subject = $template->renderBlock('subject', [
			'data' => $data,
		]);
		$message->subject($subject);

		$body = $this->engine->render($template, [
			'data' => $data,
		]);
		$message->body($body);

		call_user_func($callback, $message);
		$this->mailer->send();
	}
}