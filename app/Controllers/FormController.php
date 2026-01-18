<?php
namespace App\Controllers;

class FormController
{
    public function show(): void
    {
        $site_key = (string)cfg('recaptcha.site_key', '');
        $csrf = csrf_token();
        $site_path = site_path();

        header('Content-Type: text/html; charset=utf-8');
        render('form', [
            'siteKey' => $site_key,
            'csrf' => $csrf,
            'sitePath' => $site_path,
        ]);
    }
}
