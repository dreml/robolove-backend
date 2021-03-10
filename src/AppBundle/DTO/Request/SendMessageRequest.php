<?php
/**
 * Создано: 05.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\DTO\Request;

use JMS\Serializer\Annotation as JSA;
use Symfony\Component\Validator\Constraints as Assert;

class SendMessageRequest
{
    /**
     * @JSA\Type("string")
     * @JSA\SerializedName("message")
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @var string
     */
    public $message;

    /**
     * @JSA\Type("string")
     * @JSA\SerializedName("messageType")
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @var string
     */
    public $messageType;
}