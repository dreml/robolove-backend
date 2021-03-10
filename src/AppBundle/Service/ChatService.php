<?php
/**
 * Создано: 06.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Service;

use AppBundle\DTO\Request\SendMessageRequest;
use AppBundle\Entity\Chat;
use AppBundle\Entity\User;
use AppBundle\Repository\ChatRepository;
use AppBundle\Service\Publisher\PublisherInterface;
use Doctrine\ORM\EntityManager;

class ChatService
{
    const BROADCAST_MESSAGE_COST = 1000;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ChatRepository
     */
    private $chatRepository;

    /**
     * ChatService constructor.
     *
     * @param EntityManager $em
     * @param PublisherInterface $publisher
     * @param UserService $userService
     */
    public function __construct(
        EntityManager $em,
        PublisherInterface $publisher,
        UserService $userService
    ) {
        $this->publisher = $publisher;
        $this->userService = $userService;
        $this->chatRepository = $em->getRepository(Chat::class);
    }

    /**
     * @param User $user
     * @param SendMessageRequest $sendMessageRequest
     *
     * @return bool
     */
    public function sendChatMessage(User $user, SendMessageRequest $sendMessageRequest)
    {
        if ($sendMessageRequest->messageType == $this->publisher::BROADCAST_MESSAGE_TYPE) {
            if ($this->userService->canPayForService($user, self::BROADCAST_MESSAGE_COST)) {
                $this->userService->payForService($user, self::BROADCAST_MESSAGE_COST);
            } else {
                return false;
            }
        }

        $messageInfo = [
            'userCode' => $user->getLogin(),
            'userName' => $user->getName(),
            'message'  => $sendMessageRequest->message,
            'type'     => $sendMessageRequest->messageType,
            'postTime' => new \DateTime(),
        ];

        $message = $this->chatRepository->saveMessage($user, $messageInfo);

        $messageInfo['id'] = $message->getId();

        $this->publisher->publish(
            $messageInfo,
            $this->publisher::CHAT_TOPIC_NAME,
            $sendMessageRequest->messageType
        );

        return true;
    }
}