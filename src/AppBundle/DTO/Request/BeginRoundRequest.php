<?php
/**
 * Создано: 05.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\DTO\Request;

use JMS\Serializer\Annotation as JSA;
use Symfony\Component\Validator\Constraints as Assert;

class BeginRoundRequest
{
    /**
     * @JSA\Type("integer")
     * @JSA\SerializedName("roundNumber")
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @var int
     */
    public $roundNumber;

    /**
     * @JSA\Type("array<AppBundle\DTO\Request\ChangeSkillRequest>")
     * @JSA\SerializedName("skills")
     * @Assert\NotBlank()
     * @Assert\Valid()
     * @var ChangeSkillRequest
     */
    public $skills;
}