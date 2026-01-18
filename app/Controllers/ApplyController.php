<?php
namespace App\Controllers;

use App\Services\RecaptchaService;
use App\Services\RateLimiter;
use App\Services\UploadService;
use App\Services\MailService;
use App\Services\TelegramService;

class ApplyController
{
    public function submit(): void
    {
        try {
            if (!csrf_check($_POST['csrf_token'] ?? null)) {
                $this->fail('Сессия истекла. Обновите страницу и попробуйте снова.', 400);
            }

            if (!empty($_POST['website'] ?? '')) {
                $this->fail('Спам-фильтр сработал, бот попался!', 400);
            }

            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

            $projectRoot = dirname(__DIR__, 3);
            $storageDir  = $projectRoot . '/storage';

            $uploadsDir = $storageDir . '/uploads';
            $logsDir    = $storageDir . '/logs';
            $rateDir    = $storageDir . '/ratelimit';

            foreach ([$storageDir, $uploadsDir, $logsDir, $rateDir] as $dir) {
                if (!is_dir($dir) && !@mkdir($dir, 0700, true)) {
                        $this->fail('Ошибка сервера: нет доступа для записи (storage).', 500);
                }
            }

            $limiter = new RateLimiter($rateDir, (int)cfg('security.rate_limit_per_10min', 8));
            if (!$limiter->allow($ip.'|'.substr($ua, 0, 80))) {
                $this->fail('Слишком много попыток. Повторите позже.', 429);
            }

            $name  = $this->cleanText($_POST['name'] ?? '', 100);
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = $this->normalizePhone($_POST['phone'] ?? '');

            if ($name === '' || mb_strlen($name) < 2) {
                $this->fail('Укажите имя.', 400);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
                $this->fail('Некорректная почта.', 400);
            }
            if ($phone === '' || mb_strlen($phone) < 7 || mb_strlen($phone) > 20) {
                $this->fail('Некорректный телефон.', 400);
            }

            $recaptcha = new RecaptchaService((string)cfg('recaptcha.secret_key', ''));
            $token = (string)($_POST['g-recaptcha-response'] ?? '');
            if (!$recaptcha->verifyV2($token, $ip)) {
                $this->fail('Капча не пройдена. Повторите попытку.', 400);
            }

            $uploader = new UploadService(
                $uploadsDir,
                (int)cfg('limits.max_file_size', 10 * 1024 * 1024),
                (array)cfg('limits.allowed_ext', []),
                (array)cfg('limits.allowed_mime', [])
            );

            $saved = $uploader->save($_FILES['resume'] ?? null);

            $mail = new MailService((array)cfg('smtp', []));
            $mail->send(
                $name, $email, $phone, $ip, $ua,
                $saved['path'], $saved['name']
            );

            $tg = new TelegramService((array)cfg('telegram', []));
            $tg->notify($name, $email, $phone, $ip, $saved['path'], $saved['name']);

            $this->ok('Заявка отправлена. Спасибо!');
        } catch (\Throwable $e) {
            $this->logError($e);
            $this->fail('Не удалось отправить заявку. Попробуйте позже.', 500);
        }
    }

    private function ok(string $message): void
    {
        if (is_ajax()) {
            json_response(['ok' => true, 'message' => $message], 200);
        }
        header('Content-Type: text/html; charset=utf-8');
        echo "<h2>Готово</h2><p>". escape_html($message)."</p>";
        exit;
    }

    private function fail(string $message, int $code): void
    {
        if (\is_ajax()) \json_response(['ok' => false, 'message' => $message], $code);
        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        echo "<h2>Ошибка</h2><p>".\escape_html($message)."</p>";
        exit;
    }

    private function cleanText(mixed $s, int $maxLen): string {
        $s = trim((string)$s);
        $s = preg_replace('/[^\P{C}\t\n\r]+/u', '', $s);
        $s = preg_replace("/\r\n|\r/", "\n", $s);
        if (mb_strlen($s) > $maxLen) $s = mb_substr($s, 0, $maxLen);
        return $s;
    }

    private function normalizePhone(mixed $phone): string
    {
        $p = trim((string)$phone);
        $p = preg_replace('/(?!^\+)[^\d]/', '', $p);
        $p = preg_replace('/^\+{2,}/', '+', $p);
        return $p;
    }

    private function logError(\Throwable $e): void
    {
        $dir = dirname(__DIR__, 3) . '/storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        $line = "[".date('c')."] ".$e->getMessage()." | ".$e->getFile().":".$e->getLine()."\n";
        @file_put_contents($dir.'/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
