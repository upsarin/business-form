<?php
// Variables: $siteKey, $csrf, $sitePath
?>
<!doctype html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Отклик</title>
        <link rel="stylesheet" href="<?=escape_html($sitePath)?>/assets/style.css">
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </head>
    <body>
        <main class="wrap">
            <h1>Отклик на вакансию</h1>

            <form id="applyForm" action="<?=escape_html($sitePath)?>/submit" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?=escape_html($csrf)?>">

                <!-- honeypot (медовая липучка-ловушка выявляющая ботов и разную пакость) -->
                <div class="hp">
                    <label>Website</label>
                    <input type="text" name="website" autocomplete="off">
                </div>

                <label>Имя *</label>
                <input name="name" type="text" maxlength="100" required autocomplete="name">

                <label>Почта *</label>
                <input name="email" type="email" maxlength="150" required autocomplete="email">

                <label>Телефон *</label>
                <input name="phone" type="tel" maxlength="18" required autocomplete="tel" placeholder="+7 (913) 123-45-67">

                <label>Резюме (pdf/doc/docx/rtf) *</label>
                <input name="resume" type="file" required
                       accept=".pdf,.doc,.docx,.rtf,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">

                <div class="captcha">
                    <div class="g-recaptcha" data-sitekey="<?=escape_html($siteKey)?>"></div>
                </div>

                <button type="submit">Отправить</button>
                <p id="msg" class="msg" hidden></p>
            </form>
        </main>

        <script src="<?=escape_html($sitePath)?>/assets/app.js"></script>
    </body>
</html>
