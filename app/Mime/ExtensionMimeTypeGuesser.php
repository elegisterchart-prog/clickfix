<?php

namespace App\Mime;

use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypes;

class ExtensionMimeTypeGuesser implements MimeTypeGuesserInterface
{
    public function isGuesserSupported(): bool
    {
        // Always supported because this guesser relies only on filename extension
        return true;
    }

    public function guessMimeType(string $path): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! $ext) {
            // No extension in path (e.g. PHP temp files). Return a safe default MIME
            // so later callers that expect a string do not receive null and crash.
            return 'application/octet-stream';
        }

        // Use the built-in static map from MimeTypes for extension -> mime mapping
        try {
            $mimes = MimeTypes::getDefault()->getMimeTypes($ext);
            return $mimes[0] ?? 'application/octet-stream';
        } catch (\Throwable $e) {
            return 'application/octet-stream';
        }
    }
}
