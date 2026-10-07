<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Klien LLM Sumopod (API kompatibel OpenAI) untuk screening konten dan analisis hasil scan.
 */
class SumopodClient
{
    public function __construct(private readonly array $config) {}

    public function enabled(): bool
    {
        return filled($this->config['api_key'] ?? null);
    }

    /**
     * Kirim prompt ke model dan kembalikan respons JSON yang sudah di-decode,
     * atau null bila LLM tidak aktif, gagal dihubungi, atau responsnya tidak valid.
     */
    public function chatJson(string $systemPrompt, string $userPrompt): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        $endpoint = rtrim((string) $this->config['base_url'], '/').'/chat/completions';

        try {
            $response = Http::withToken($this->config['api_key'])
                ->acceptJson()
                ->timeout((int) ($this->config['timeout'] ?? 60))
                ->retry(2, 1000, throw: false)
                ->post($endpoint, [
                    'model' => $this->config['model'] ?? 'gpt-4o-mini',
                    'temperature' => 0,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning("Permintaan LLM Sumopod gagal (HTTP {$response->status()}): ".$response->body());

                return null;
            }

            $decoded = json_decode((string) $response->json('choices.0.message.content'), true);

            return is_array($decoded) ? $decoded : null;
        } catch (Throwable $e) {
            Log::warning("Permintaan LLM Sumopod gagal: {$e->getMessage()}");

            return null;
        }
    }
}
