<?php

function save_quiz_image(?array $file): string
{
    if ($file === null || ($file['error'] ?? null) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($file['error'] ?? null) !== UPLOAD_ERR_OK ||
        !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('The image could not be uploaded.');
    }
    $size = filesize($file['tmp_name']);
    if ($size === false || $size < 1 || $size > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Each image must be no larger than 5 MB.');
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $info = @getimagesize($file['tmp_name']);
    if (!isset($types[$mime]) || !$info || ($info['mime'] ?? '') !== $mime) {
        throw new InvalidArgumentException('Upload a JPEG, PNG, GIF, or WebP image.');
    }
    $directory = __DIR__ . '/../main/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Upload directory is unavailable.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('The image could not be saved.');
    }
    return $filename;
}
