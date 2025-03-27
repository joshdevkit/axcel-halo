<?php

namespace App\Core\Validators\Rules;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageRule
{
    protected array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        return ($value instanceof UploadedFile && in_array($value->getMimeType(), $this->allowedMimeTypes))
            ? null
            : ucfirst($field) . " must be a valid image file (jpeg, png, gif).";
    }
}
