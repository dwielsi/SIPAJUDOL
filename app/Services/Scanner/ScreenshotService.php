<?php

namespace App\Services\Scanner;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Throwable;

class ScreenshotService
{
    public function __construct(private readonly array $config) {}

    public function capture(string $url, string $filenameHint): ?string
    {
        if (! ($this->config['enabled'] ?? true)) {
            return null;
        }

        $disk = $this->config['disk'] ?? 'public';
        $path = 'screenshots/'.Str::slug($filenameHint).'-'.now()->timestamp.'.png';

        try {
            $browsershot = Browsershot::url($url)
                ->windowSize((int) ($this->config['width'] ?? 1280), (int) ($this->config['height'] ?? 800))
                ->timeout((int) ($this->config['timeout'] ?? 30))
                ->noSandbox()
                ->ignoreHttpsErrors()
                ->setOption('args', ['--disable-gpu'])
                ->fullPage();

            if (! empty($this->config['node_binary'])) {
                $browsershot->setNodeBinary($this->config['node_binary']);
            }

            if (! empty($this->config['npm_binary'])) {
                $browsershot->setNpmBinary($this->config['npm_binary']);
            }

            if (! empty($this->config['node_modules_path'])) {
                $browsershot->setNodeModulePath($this->config['node_modules_path']);
            }

            if (! empty($this->config['chrome_path'])) {
                $browsershot->setChromePath($this->config['chrome_path']);
            }

            $image = $browsershot->screenshot();

            Storage::disk($disk)->put($path, $image);

            return $path;
        } catch (Throwable $e) {
            Log::warning("Gagal mengambil screenshot untuk {$url}: {$e->getMessage()}");

            return null;
        }
    }
}
