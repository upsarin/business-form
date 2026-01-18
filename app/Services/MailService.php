<?php
namespace App\Services;

if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
    $base = __DIR__ . '/../../lib/PHPMailer/src/';
    if (is_dir($base)) {
        require_once $base . 'Exception.php';
        require_once $base . 'PHPMailer.php';
        require_once $base . 'SMTP.php';
    }
}

use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    private array $cfg;

    public function __construct(array $cfg) {
        $this->cfg = $cfg;
    }

    public function send(
        string $name, string $email, string $phone,
        string $ip, string $ua,
        string $attachmentPath, string $attachmentName
    ): void
    {
        $smtp = $this->cfg;

        foreach (['host','port','secure','username','password','from_email','from_name','to_email','to_name'] as $k) {
            if (!isset($smtp[$k]) || $smtp[$k] === '') {
                throw new \RuntimeException("SMTP config missing: {$k}");
            }
        }

        $body =
            "Новая заявка с формы\n\n".
            "Имя: {$name}\n".
            "Email: {$email}\n".
            "Телефон: {$phone}\n".
            "IP: {$ip}\n".
            "UA: {$ua}\n";

        $m = new PHPMailer(true);
        $m->CharSet = 'UTF-8';
        $m->isSMTP();
        $m->Host = (string)$smtp['host'];
        $m->Port = (int)$smtp['port'];
        $m->SMTPAuth = true;
        $m->Username = (string)$smtp['username'];
        $m->Password = (string)$smtp['password'];
        $m->SMTPSecure = (string)$smtp['secure'];

        $m->setFrom((string)$smtp['from_email'], (string)$smtp['from_name']);
        $m->addAddress((string)$smtp['to_email'], (string)$smtp['to_name']);
        $m->addReplyTo($email, $name);

        $m->Subject = 'Новая заявка - ' . $name;
        $m->Body = $body;

        $m->addAttachment($attachmentPath, $attachmentName);
        $m->send();
    }
}
