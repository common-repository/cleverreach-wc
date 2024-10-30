<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpResponse;

/**
 * Class Proxy
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http
 */
abstract class Proxy
{
    /**
     * Base CleverReach API URL.
     */
    const BASE_API_URL = 'https://rest.cleverreach.com/';
    /**
     * Used API Version.
     */
    const API_VERSION = 'v3';
    /**
     * Http client instance.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient
     */
    protected $httpClient;
    /**
     * Authorization service instance
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService
     */
    protected $authService;

    /**
     * Proxy constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient $httpClient
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService $authService
     */
    public function __construct(
        HttpClient $httpClient,
        AuthorizationService $authService = null
    ) {
        $this->httpClient = $httpClient;
        $this->authService = $authService;
    }

    /**
     * Performs GET HTTP request.
     *
     * @param string $endpoint Get request endpoint.
     * @param array<string,string> $headers List of additional request headers.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpResponse Get HTTP response.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function get($endpoint, array $headers = array())
    {
        return $this->call(HttpClient::HTTP_METHOD_GET, $endpoint, array(), $headers);
    }

    /**
     * Performs DELETE HTTP request.
     *
     * @param string $endpoint DELETE request endpoint.
     * @param array<string,string> $headers List of additional request headers.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpResponse DELETE HTTP response.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function delete($endpoint, array $headers = array())
    {
        return $this->call(HttpClient::HTTP_METHOD_DELETE, $endpoint, array(), $headers);
    }

    /**
     * Performs POST HTTP request.
     *
     * @param string $endpoint POST request endpoint.
     * @param mixed[] $body POST request body.
     * @param array<string,string> $headers Additional list of POST request headers.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpResponse Response instance.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function post($endpoint, array $body = array(), array $headers = array())
    {
        return $this->call(HttpClient::HTTP_METHOD_POST, $endpoint, $body, $headers);
    }

    /**
     * Performs PUT HTTP request.
     *
     * @param string $endpoint PUT request endpoint.
     * @param mixed[] $body PUT request body.
     * @param array<string,string> $headers Additional list of PUT request headers.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpResponse Response instance.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function put($endpoint, array $body = array(), array $headers = array())
    {
        return $this->call(HttpClient::HTTP_METHOD_PUT, $endpoint, $body, $headers);
    }

    /**
     * Performs PATCH HTTP request.
     *
     * @param string $endpoint PATCH request endpoint.
     * @param mixed[] $body PATCH request body.
     * @param array<string,string> $headers Additional list of PATCH request headers.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpResponse Response instance.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function patch($endpoint, array $body = array(), array $headers = array())
    {
        return $this->call(HttpClient::HTTP_METHOD_PATCH, $endpoint, $body, $headers);
    }

    /**
     * Performs HTTP call.
     *
     * @param string $method Specifies which http method is utilized in call.
     * @param string $endpoint Specifies which endpoint is called.
     * @param mixed[] $body Specifies request body.
     * @param array<string,string> $headers Specifies additional request headers.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpResponse Response instance.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function call($method, $endpoint, array $body = array(), array $headers = array())
    {
        $bodyStringToSend = '';
        $hasDataToSend = $this->canSendData($method);
        if ($hasDataToSend && !empty($body)) {
            $bodyStringToSend = json_encode($body) ?: '';
        }

        $response = $this->httpClient->request(
            $method,
            $this->getUrl($endpoint),
            array_merge($headers, $this->getHeaders()),
            $bodyStringToSend
        );

        $this->validateResponse($response);

        return $response;
    }

    /**
     * Checks whether request data can be sent or not.
     *
     * @param string $method HTTP Method identifier.
     *
     * @return bool Identifies whether request data can be sent.
     */
    protected function canSendData($method)
    {
        return in_array(
            strtoupper($method),
            array(HttpClient::HTTP_METHOD_POST, HttpClient::HTTP_METHOD_PUT, HttpClient::HTTP_METHOD_PATCH),
            true
        );
    }

    /**
     * Retrieves full request url.
     *
     * @param string $endpoint Endpoint identifier.
     *
     * @return string Full request url.
     */
    protected function getUrl($endpoint)
    {
        return self::BASE_API_URL . self::API_VERSION . '/' . ltrim(trim($endpoint), '/');
    }

    /**
     * Retrieves request headers.
     *
     * @return array<string,string> Complete list of request headers.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     */
    protected function getHeaders()
    {
        return array_merge($this->getBaseHeaders(), $this->getAuthHeaders());
    }

    /**
     * Retrieves base request headers.
     *
     * @return array<string,string> Array containing base request headers.
     */
    protected function getBaseHeaders()
    {
        return array(
            'accept' => 'Accept: application/json',
            'content' => 'Content-Type: application/json',
        );
    }

    /**
     * Retrieves auth headers.
     *
     * @return array<string,string>
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     */
    protected function getAuthHeaders()
    {
        return array(
            'token' => 'Authorization: Bearer ' . $this->getAccessToken(),
        );
    }

    /**
     * Retrieves access token.
     *
     * @return string
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     */
    protected function getAccessToken()
    {
        return $this->authService->getAuthInfo()->getAccessToken();
    }

    /**
     * Validates HTTP response.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpResponse $response Response object to be validated.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function validateResponse(HttpResponse $response)
    {
        if ($response->isSuccessful()) {
            return;
        }

        $message = $body = $response->getBody();

        $httpCode = $response->getStatus();
        $responseBody = json_decode($body, true);
        if (is_array($responseBody)) {
            if (isset($responseBody['error']['code'])) {
                $httpCode = $responseBody['error']['code'];
            }

            if (isset($responseBody['error']['message'])) {
                $message = $responseBody['error']['message'];
            }
        }

        throw new HttpRequestException($message, $httpCode);
    }
}
