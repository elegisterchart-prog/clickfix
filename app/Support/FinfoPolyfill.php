<?php

// Minimal polyfill for the native finfo class when the php_fileinfo extension is not available.
// This provides only enough behavior for libraries that call (new \finfo(FILEINFO_MIME_TYPE))->file($path)
// It guesses MIME type based on file extension as a fallback.

if (! class_exists('finfo')) {
    if (! defined('FILEINFO_MIME_TYPE')) {
        define('FILEINFO_MIME_TYPE', 16);
    }

    class finfo
    {
        protected $options;
        public function __construct($options = 0, $db = null)
        {
            $this->options = $options;
        }

        public function file(string $path)
        {
            // If file doesn't exist, return false (mirrors native finfo behavior)
            if (! is_string($path) || ! file_exists($path)) {
                return false;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (! $ext) {
                return 'application/octet-stream';
            }

            try {
                $mimes = \Symfony\Component\Mime\MimeTypes::getDefault()->getMimeTypes($ext);
                return $mimes[0] ?? 'application/octet-stream';
            } catch (\Throwable $e) {
                return 'application/octet-stream';
            }
        }

        // Some code might call finfo::buffer; provide minimal implementation
        public function buffer(string $string)
        {
            // We cannot inspect binary signature reliably here; fallback to octet-stream
            return 'application/octet-stream';
        }
    }
}
