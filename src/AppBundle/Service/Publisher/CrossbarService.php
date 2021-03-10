<?php
/**
 * Создано: 01.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Service\Publisher;

use AppBundle\DTO\Request\SendMessageRequest;
use AppBundle\Entity\User;
use Facile\CrossbarHTTPPublisherBundle\Publisher\Publisher;

class CrossbarService implements PublisherInterface
{
    /**
     * @var string
     */
    private $topicPrefix;

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * CrossbarService constructor.
     */
    public function __construct(
        string $topicPrefix,
        Publisher $publisher
    ) {
        $this->topicPrefix = $topicPrefix;
        $this->publisher = $publisher;
    }

    public function publish(array $message, string $topicName, string $messageType)
    {
        $message['messageType'] = $messageType;
        $response = $this->publisher->publish($this->topicPrefix . $topicName, [$message]);
    }

}