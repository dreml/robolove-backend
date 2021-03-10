<?php
/**
 * Создано: 05.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\DTO\Request;

use JMS\Serializer\Annotation as JSA;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeSkillRequest
{
    /**
     * @JSA\Type("integer")
     * @JSA\SerializedName("skillId")
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @var int
     */
    public $skillId;

    /**
     * @JSA\Type("string")
     * @JSA\SerializedName("skillValue")
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @var string
     */
    public $skillValue;
}