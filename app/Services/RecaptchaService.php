<?php
namespace App\Services;

class RecaptchaService
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function verifyV2(string $token, ?string $remoteIp = null): bool
    {
        if ($this->secret === '' || $token === '') return false;

        $post = ['secret' => $this->secret, 'response' => $token];
        if ($remoteIp) $post['remoteip'] = $remoteIp;

        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($post),
            CURLOPT_TIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        if (!is_string($resp)) {
            return false;
        }
        $json = json_decode($resp, true);
        return is_array($json) && !empty($json['success']);
    }
}
