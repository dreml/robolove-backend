<?php
/**
 * Создано: 29.01.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\DTO\Request;

use JMS\Serializer\Annotation as JSA;
use Symfony\Component\Validator\Constraints as Assert;

class SaveProfileRequest
{
    /**
     * @JSA\Type("string")
     * @JSA\SerializedName("nickname")
     * @Assert\Type(type="string")
     * @var string
     */
    public $nickname;

    /**
     * @JSA\Type("integer")
     * @JSA\SerializedName("robotModel")
     * @Assert\Type(type="integer")
     * @var int
     */
    public $robotModel;

    /**
     * @JSA\Type("string")
     * @JSA\SerializedName("robotColor")
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @var string
     */
    public $robotColor;
}