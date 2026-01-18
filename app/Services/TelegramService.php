<?php
namespace App\Services;

class TelegramService
{
    private string $token;
    private string $chatId;

    public function __construct(array $cfg)
    {
        $this->token = (string)($cfg['bot_token'] ?? '');
        $this->chatId = (string)($cfg['chat_id'] ?? '');
    }

    public function notify(
        string $name, string $email, string $phone, string $ip,
        string $filePath, string $fileName
    ): void
    {
        if ($this->token === '' || $this->chatId === '') return;

        $text =
            "Новая заявка\n".
            "Имя: {$name}\n".
            "Email: {$email}\n".
            "Телефон: {$phone}\n".
            "IP: {$ip}";

        $this->api('sendMessage', [
            'chat_id' => $this->chatId,
            'text' => $text,
        ]);

        if (is_file($filePath)) {
            $this->sendDocument($filePath, $fileName, "Резюме: {$name}");
        }
    }

    private function api(string $method, array $data): void
    {
        $url = "https://api.telegram.org/bot{$this->token}/{$method}";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_TIMEOUT => 15,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function sendDocument(string $path, string $name, string $caption): void
    {
        $url = "https://api.telegram.org/bot{$this->token}/sendDocument";

        $mime = 'application/octet-stream';
        if (class_exists(\finfo::class)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($path) ?: $mime;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_POSTFIELDS => [
                'chat_id' => $this->chatId,
                'caption' => $caption,
                'document' => new \CURLFile($path, $mime, $name),
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
