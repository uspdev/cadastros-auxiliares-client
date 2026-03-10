<?php

namespace Uspdev\CadastrosAuxiliaresClient\Services;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Uspdev\CadastrosAuxiliaresClient\Contracts\ProgramasClientInterface;

class ProgramasClient implements ProgramasClientInterface
{
    public function __construct(
        private readonly ConfigRepository $config
    ) {
    }

    public function listar(): Collection
    {
        $endpoint = $this->baseEndpoint();

        if ($endpoint === '') {
            return collect();
        }

        try {
            $response = $this->httpRequest()->get($endpoint);

            if (!$response->ok()) {
                return collect();
            }

            $payload = $response->json();

            if (!is_array($payload)) {
                return collect();
            }

            return collect($payload)
                ->filter(static fn ($item) => is_array($item))
                ->values();
        } catch (\Throwable) {
            return collect();
        }
    }

    public function obter(int $codcur): ?array
    {
        $endpoint = $this->baseEndpoint();

        if ($endpoint === '') {
            return null;
        }

        try {
            $response = $this->httpRequest()->get($endpoint . '/' . $codcur);

            if (!$response->ok()) {
                return null;
            }

            $payload = $response->json();

            return is_array($payload) ? $payload : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function baseEndpoint(): string
    {
        $endpoint = trim((string) $this->config->get('cadastros-auxiliares-client.programas.endpoint_url', ''));

        if ($endpoint !== '') {
            $queryString = parse_url($endpoint, PHP_URL_QUERY) ?: '';
            $baseEndpoint = $queryString === '' ? $endpoint : str_replace('?' . $queryString, '', $endpoint);

            return rtrim($baseEndpoint, '/');
        }

        $baseUrl = rtrim(trim((string) $this->config->get('cadastros-auxiliares-client.base_url', '')), '/');

        if ($baseUrl === '') {
            return '';
        }

        return $baseUrl . '/api/pos/programas';
    }

    private function httpRequest(): PendingRequest
    {
        $timeout = max(1, (int) $this->config->get('cadastros-auxiliares-client.programas.timeout', 5));
        $retryTimes = max(0, (int) $this->config->get('cadastros-auxiliares-client.programas.retry_times', 1));
        $retrySleepMs = max(0, (int) $this->config->get('cadastros-auxiliares-client.programas.retry_sleep_ms', 150));

        $request = Http::acceptJson()->timeout($timeout);

        if ($retryTimes > 0) {
            $request = $request->retry($retryTimes, $retrySleepMs, throw: false);
        }

        $password = trim((string) $this->config->get(
            'cadastros-auxiliares-client.programas.password',
            $this->config->get('cadastros-auxiliares-client.mensagens.password', '')
        ));

        if ($password !== '') {
            $request = $request->withHeaders([
                'X-Cadastros-Auxiliares-Password' => $password,
            ]);
        }

        return $request;
    }
}
