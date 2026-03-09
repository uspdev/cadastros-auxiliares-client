<?php

namespace Uspdev\CadastrosAuxiliaresClient\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Uspdev\CadastrosAuxiliaresClient\Contracts\MensagensClientInterface;

class MensagensClient implements MensagensClientInterface
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly CacheRepository $cache
    ) {
    }

    public function fetch(array $filters = []): Collection
    {
        $enabled = filter_var(
            $this->config->get('cadastros-auxiliares-client.enabled', false),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) ?? false;

        if (!$enabled) {
            return collect();
        }

        [$endpoint, $query] = $this->buildRequest($filters);

        if ($endpoint === '') {
            return collect();
        }

        if (!$this->cacheEnabled()) {
            return $this->requestMensagens($endpoint, $query) ?? collect();
        }

        $cacheKey = $this->cacheKey($endpoint, $query);
        $stale = $this->cachedMessages($cacheKey);
        $fresh = $this->requestMensagens($endpoint, $query);

        if ($fresh !== null) {
            $this->cache->put($cacheKey, $fresh->all(), now()->addSeconds($this->cacheTtlSeconds()));

            return $fresh;
        }

        return $stale;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{string, array<string, scalar>}
     */
    private function buildRequest(array $filters): array
    {
        $endpoint = trim((string) $this->config->get('cadastros-auxiliares-client.mensagens.endpoint_url', ''));

        if ($endpoint === '') {
            return ['', []];
        }

        $queryString = parse_url($endpoint, PHP_URL_QUERY) ?: '';
        parse_str($queryString, $endpointQuery);
        $baseEndpoint = $queryString === '' ? $endpoint : str_replace('?' . $queryString, '', $endpoint);

        $defaultQuery = [
            'ativos' => true,
            'limite' => max(1, (int) $this->config->get('cadastros-auxiliares-client.mensagens.limite', 5)),
        ];

        $sistema = trim((string) $this->config->get('cadastros-auxiliares-client.mensagens.sistema', ''));

        if ($sistema !== '') {
            $defaultQuery['sistema'] = $sistema;
        }

        $query = array_replace($defaultQuery, $endpointQuery, $filters);

        return [$baseEndpoint, $this->sanitizeQuery($query)];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, scalar>
     */
    private function sanitizeQuery(array $query): array
    {
        $sanitized = [];

        if (array_key_exists('limite', $query)) {
            $sanitized['limite'] = max(1, min((int) $query['limite'], 100));
        }

        if (array_key_exists('sistema', $query)) {
            $sistema = trim((string) $query['sistema']);

            if ($sistema !== '') {
                $sanitized['sistema'] = $sistema;
            }
        }

        if (array_key_exists('ativos', $query)) {
            $ativos = $this->normalizeBooleanQueryValue($query['ativos']);

            if ($ativos !== null) {
                $sanitized['ativos'] = $ativos;
            }
        }

        if (array_key_exists('publico', $query)) {
            $publico = $query['publico'];

            if (is_bool($publico)) {
                $sanitized['publico'] = $publico ? 'true' : 'false';
            } elseif (is_string($publico)) {
                $publico = trim($publico);

                if ($publico !== '') {
                    $sanitized['publico'] = $publico;
                }
            }
        }

        return $sanitized;
    }

    private function normalizeBooleanQueryValue(mixed $value): ?string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            $intValue = (int) $value;

            if ($intValue === 1 || $intValue === 0) {
                return $intValue === 1 ? 'true' : 'false';
            }
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return null;
            }

            $parsed = filter_var($trimmed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($parsed !== null) {
                return $parsed ? 'true' : 'false';
            }
        }

        return null;
    }

    /**
     * @param  array<string, scalar>  $query
     * @return Collection<int, array<string, mixed>>|null
     */
    private function requestMensagens(string $endpoint, array $query): ?Collection
    {
        try {
            $response = $this->httpRequest()->get($endpoint, $query);

            if (!$response->ok()) {
                return null;
            }

            $payload = $response->json();

            if (!is_array($payload)) {
                return null;
            }

            return collect($payload)
                ->filter(static fn ($item) => is_array($item))
                ->values();
        } catch (\Throwable) {
            return null;
        }
    }

    private function httpRequest(): PendingRequest
    {
        $timeout = max(1, (int) $this->config->get('cadastros-auxiliares-client.mensagens.timeout', 5));
        $retryTimes = max(0, (int) $this->config->get('cadastros-auxiliares-client.mensagens.retry_times', 1));
        $retrySleepMs = max(0, (int) $this->config->get('cadastros-auxiliares-client.mensagens.retry_sleep_ms', 150));

        $request = Http::acceptJson()->timeout($timeout);

        if ($retryTimes > 0) {
            $request = $request->retry($retryTimes, $retrySleepMs, throw: false);
        }

        $password = trim((string) $this->config->get('cadastros-auxiliares-client.mensagens.password', ''));

        if ($password !== '') {
            $request = $request->withHeaders([
                'X-Cadastros-Auxiliares-Password' => $password,
            ]);
        }

        return $request;
    }

    /**
     * @param  array<string, scalar>  $query
     */
    private function cacheKey(string $endpoint, array $query): string
    {
        ksort($query);

        $prefix = trim((string) $this->config->get(
            'cadastros-auxiliares-client.mensagens.cache.prefix',
            'cadastros-auxiliares-client:mensagens'
        ));

        if ($prefix === '') {
            $prefix = 'cadastros-auxiliares-client:mensagens';
        }

        return $prefix . ':' . md5($endpoint . '|' . json_encode($query));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function cachedMessages(string $cacheKey): Collection
    {
        $cached = $this->cache->get($cacheKey, []);

        if (!is_array($cached)) {
            return collect();
        }

        return collect($cached)
            ->filter(static fn ($item) => is_array($item))
            ->values();
    }

    private function cacheEnabled(): bool
    {
        $enabled = filter_var(
            $this->config->get('cadastros-auxiliares-client.mensagens.cache.enabled', true),
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        );

        return ($enabled ?? true) && $this->cacheTtlSeconds() > 0;
    }

    private function cacheTtlSeconds(): int
    {
        return max(1, (int) $this->config->get('cadastros-auxiliares-client.mensagens.cache.ttl_seconds', 30));
    }
}
