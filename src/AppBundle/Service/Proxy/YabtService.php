<?php

namespace AppBundle\Service\Proxy;

use AppBundle\DTO;

/**
 * Сервис для работы с сервисом YABT
 *
 * Class YabtService
 *
 * @package AppBundle\Service\Proxy
 */
class YabtService extends JsonProxy
{

    /**
     * Возвращает информацию о пользователе
     *
     * @param string $token
     *
     * @return DTO\Proxy\UserInfoResponse
     *
     * @throws \Exception
     */
    public function getUserInfo(string $token)
    {
        $url = $this->buildUrl('/identity/connect/userinfo');

        $response = $this->getResponseFromPostRequest($url, [], DTO\Proxy\UserInfoResponse::class, ['Authorization' => $token]);

        if ($response instanceof DTO\Proxy\UserInfoResponse) {
            if (empty($response->sub)) {
                throw new \Exception('Некорректный токен');
            }
        } else {
            throw new \Exception('Некорректный токен');
        }

        return $response;
    }

}