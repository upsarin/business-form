<?php
namespace App\Services;

class UploadService
{
    private string $baseDir;
    private int $maxSize;
    private array $allowedExt;
    private array $allowedMime;

    public function __construct(string $baseDir, int $maxSize, array $allowedExt, array $allowedMime)
    {
        $this->baseDir = rtrim($baseDir, '/');
        $this->maxSize = $maxSize;
        $this->allowedExt = array_map('strtolower', $allowedExt);
        $this->allowedMime = $allowedMime;

        if (!is_dir($this->baseDir) && !@mkdir($this->baseDir, 0700, true)) { // ???
            throw new \RuntimeException('Не удалось создать директорию загрузок.');
        }
    }

    public function save(?array $file): array
    {
        if (!$file || !isset($file['error'])) {
            throw new \RuntimeException('Файл резюме не прикреплён.');
        }
        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Ошибка загрузки файла.');
        }

        if ((int)$file['size'] <= 0 || (int)$file['size'] > $this->maxSize) {
            throw new \RuntimeException('Файл слишком большой или пустой.');
        }

        $tmp = (string)$file['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            throw new \RuntimeException('Подмена файла не допускается.');
        }

        $orig = (string)$file['name'];
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExt, true)) {
            throw new \RuntimeException('Недопустимое расширение файла.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: 'application/octet-stream';
        if (!in_array($mime, $this->allowedMime, true)) {
            throw new \RuntimeException('Недопустимый тип файла.');
        }

        $subdir = $this->baseDir . '/' . date('Y-m');
        if (!is_dir($subdir) && !@mkdir($subdir, 0700, true)) {
            throw new \RuntimeException('Не удалось создать директорию для загрузки.');
        }

        $safe = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = $subdir . '/' . $safe;

        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('Не удалось сохранить файл.');
        }
        @chmod($dest, 0600);

        return ['path' => $dest, 'name' => 'resume.' . $ext, 'mime' => $mime];
    }
}
