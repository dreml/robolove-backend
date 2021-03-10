<?php
/**
 * Создано: 01.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\DTO\Response;

use JMS\Serializer\Annotation as JSA;

class ProfileResponse
{
    /**
     * @JSA\Type("string")
     * @var string
     */
    public $token;

    /**
     * @JSA\Type("string")
     * @var string
     */
    public $name;

    /**
     * @JSA\Type("integer")
     * @var int
     */
    public $robotModel;

    /**
     * @JSA\Type("string")
     * @var string
     */
    public $robotColor;

    /**
     * @JSA\Type("string")
     * @var string
     */
    public $nickname;

    /**
     * @JSA\Type("string")
     * @var string
     */
    public $currentPoints;

    /**
     * @JSA\Type("integer")
     * @var int
     */
    public $ratingPosition;

    /**
     * @JSA\Type("boolean")
     * @var bool
     */
    public $vip;
}