<?php
/**
 * Создано: 06.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Service\Publisher;

interface PublisherInterface
{
    const LOBBY_TOPIC_NAME = 'lobby';
    const CHAT_TOPIC_NAME = 'chat';
    const MATCH_TOPIC_PREFIX = 'match_';

    const LOBBY_MESSAGE_TYPE = 'lobbyMessage';
    const BROADCAST_MESSAGE_TYPE = 'broadcastMessage';
    const BEGINNING_OF_MATCH_MESSAGE_TYPE = 'beginningOfMatch';
    const SKILL_CHANGE_MESSAGE_TYPE = 'skillChange';
    const ROUND_RESULT_MESSAGE_TYPE = 'roundResult';
    const MATCH_RESULT_MESSAGE_TYPE = 'matchResult';
    const RATING_MESSAGE_TYPE = 'rating';

    /**
     * @param array $message
     * @param string $topic
     * @param string $messageType
     *
     * @return mixed
     */
    public function publish(array $message, string $topic, string $messageType);
}