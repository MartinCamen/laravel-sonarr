<?php

namespace MartinCamen\LaravelSonarr\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use MartinCamen\ArrCore\Client\RestClientInterface;
use MartinCamen\ArrCore\Contract\Endpoint;
use MartinCamen\ArrCore\Exceptions\AuthenticationException;
use MartinCamen\ArrCore\Exceptions\ConnectionException as SonarrConnectionException;
use MartinCamen\ArrCore\Exceptions\NotFoundException;
use MartinCamen\ArrCore\Exceptions\ValidationException;
use MartinCamen\Sonarr\Config\SonarrConfiguration;

/**
 * Laravel HTTP Client adapter for Sonarr REST API.
 *
 * @link https://wiki.servarr.com/sonarr/api
 */
class LaravelRestClient implements RestClientInterface
{
    public function __construct(protected SonarrConfiguration $config) {}

    /**
     * @param array<string, mixed> $params
     *
     * @throws AuthenticationException
     * @throws SonarrConnectionException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function get(Endpoint $endpoint, array $params = []): mixed
    {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws AuthenticationException
     * @throws SonarrConnectionException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function post(Endpoint $endpoint, array $data = []): mixed
    {
        return $this->request('POST', $endpoint, body: $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws AuthenticationException
     * @throws SonarrConnectionException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function put(Endpoint $endpoint, array $data = []): mixed
    {
        return $this->request('PUT', $endpoint, body: $data);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws AuthenticationException
     * @throws SonarrConnectionException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function delete(Endpoint $endpoint, array $params = []): mixed
    {
        return $this->request('DELETE', $endpoint, $params);
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     *
     * @throws AuthenticationException
     * @throws SonarrConnectionException
     * @throws NotFoundException
     * @throws ValidationException
     */
    protected function request(
        string $method,
        Endpoint $endpoint,
        array $query = [],
        array $body = [],
    ): mixed {
        $pathParams = $this->extractPathParams($endpoint, $query);
        $url = $this->buildUrl($endpoint, $pathParams);

        try {
            $request = $this->buildRequest();

            $response = match ($method) {
                'GET'    => $request->get($url, $query),
                'POST'   => $request->post($url, $body),
                'PUT'    => $request->put($url, $body),
                'DELETE' => $request->delete($url, $query),
                default  => throw new InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            $response->throw();

            $contents = $response->body();

            if ($contents === '') {
                return null;
            }

            return $response->json();
        } catch (ConnectionException $e) {
            throw SonarrConnectionException::failed(
                $this->config->host,
                $this->config->port,
                $e->getMessage(),
            );
        } catch (RequestException $e) {
            $status = $e->response->status();
            $responseBody = $e->response->json();

            return match ($status) {
                401      => throw AuthenticationException::invalidApiKey(),
                404      => throw NotFoundException::resourceNotFound($endpoint->path($pathParams)),
                400, 422 => throw ValidationException::fromResponse($responseBody),
                default  => throw SonarrConnectionException::failed(
                    $this->config->host,
                    $this->config->port,
                    $e->getMessage(),
                ),
            };
        }
    }

    protected function buildRequest(): PendingRequest
    {
        return Http::withHeaders([
            'X-Api-Key' => $this->config->apiKey,
        ])
            ->acceptJson()
            ->contentType('application/json')
            ->timeout($this->config->timeout);
    }

    /**
     * Extract path parameters from query and return them separately.
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function extractPathParams(Endpoint $endpoint, array &$query): array
    {
        $pathParams = [];
        $path = $endpoint->path();

        preg_match_all('/\{(\w+)\}/', $path, $matches);

        foreach ($matches[1] as $param) {
            if (isset($query[$param])) {
                $pathParams[$param] = $query[$param];
                unset($query[$param]);
            }
        }

        return $pathParams;
    }

    /** @param array<string, mixed> $pathParams */
    protected function buildUrl(Endpoint $endpoint, array $pathParams): string
    {
        $path = $endpoint->path($pathParams);

        return "{$this->config->getBaseUrl()}/{$path}";
    }
}
