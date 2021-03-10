<?php

namespace AppBundle\Service\Proxy;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use Symfony\Component\Config\Definition\Exception\Exception;
use Psr\Log\LoggerInterface;

/**
 * Общая прокся для всех json-сервисов
 *
 * Class JsonProxy
 *
 * @package AppBundle\Service
 */
abstract class JsonProxy
{
    /**
     * API url
     *
     * @var string
     */
    private $url;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * JsonProxy constructor.
     *
     * @param string $url
     * @param Serializer $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(string $url, Serializer $serializer, LoggerInterface $logger)
    {
        $this->client = new Client();
        $this->url = $url;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function buildUrl(string $path): string
    {
        return $this->url . $path;
    }

    /**
     * @param string $url
     * @param array $request
     */
    protected function logRequest($url, $request)
    {
        if (null !== $this->logger) {
            $context = [
                'url'     => $url,
                'request' => $request,
            ];
            $this->logger->info('Отправлен запрос', $context);
        }
    }

    /**
     * @param string $url
     * @param array $request
     * @param string $response
     */
    protected function logSuccess($url, $request, $response)
    {
        if (null !== $this->logger) {
            $context = [
                'url'      => $url,
                'request'  => $request,
                'response' => $response,
            ];
            $this->logger->info('Получен ответ на исходящий запрос', $context);
        }
    }

    /**
     * Логирование исключений
     *
     * @param \Exception $e
     * @param $url
     * @param $request
     */
    protected function logException(\Exception $e, $url, $request)
    {
        if (null !== $this->logger) {
            $context = [
                'url'          => $url,
                'request'      => $request,
                'errorMessage' => $e->getMessage(),
                'exception'    => $e,
            ];
            $this->logger->error('Ошибка при исходящем запросе', $context);
        }
    }

    /**
     * Возвращает результат POST запроса
     *
     * @param string $url
     * @param array|object $data
     * @param string $classResponse
     * @param array|null $headers
     *
     * @return object|null
     */
    protected function getResponseFromPostRequest(string $url, $data, string $classResponse, $headers = [])
    {
        $responseDeserialized = null;

        try {
            $request = $this->serializer->toArray($data);
            $this->logRequest($url, $request);

            $httpfulResponse = $this->client->request(
                'POST',
                $url,
                [
                    'headers' => array_merge(
                        [
                            'Content-type'  => 'application/json',
                            'Cache-control' => 'no-cache',
                        ],
                        $headers
                    ),
                    'json'    => $request,
                ]
            );

            $response = $httpfulResponse->getBody()->getContents();

            $responseDeserialized = $this->serializer->deserialize(
                $response,
                $classResponse,
                'json'
            );

            $this->logSuccess($url, $request, $response);
        } catch (\Exception $e) {
            $this->logException($e, $url, $request);
        }

        return $responseDeserialized;
    }
}