<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ensure our finfo polyfill (if needed) is loaded early so vendor code
        // that references the global finfo class doesn't fail.
        $polyfill = app_path('Support') . DIRECTORY_SEPARATOR . 'FinfoPolyfill.php';
        if (file_exists($polyfill)) {
            require_once $polyfill;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register a fallback MIME guesser based on file extension to avoid
        // Symfony\Mime\MimeTypes throwing a LogicException when the
        // php_fileinfo extension is not available in the web SAPI.
        try {
            if (class_exists(\Symfony\Component\Mime\MimeTypes::class) && class_exists(\App\Mime\ExtensionMimeTypeGuesser::class)) {
                \Symfony\Component\Mime\MimeTypes::getDefault()->registerGuesser(new \App\Mime\ExtensionMimeTypeGuesser());
            }
        } catch (\Throwable $e) {
            // Best-effort: don't break the app boot if registering fails
            logger('mime-fallback-register-error: '.$e->getMessage());
        }
    }
}
