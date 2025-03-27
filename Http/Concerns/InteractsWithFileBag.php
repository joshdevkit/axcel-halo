<?php

namespace App\Core\Http\Concerns;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait InteractsWithFileBag
{
    public function hasFile($key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function file($key)
    {
        if ($this->hasFile($key)) {
            return new UploadedFile(
                $_FILES[$key]['tmp_name'],
                $_FILES[$key]['name'],
                $_FILES[$key]['type'],
                $_FILES[$key]['size'],
                $_FILES[$key]['error']
            );
        }

        return null;
    }
}
