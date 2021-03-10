<?php
/**
 * Создано: 01.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\DTO\Proxy;

use JMS\Serializer\Annotation as JSA;

class UserInfoResponse
{
    /**
     * @JSA\Type("string")
     * @var string
     */
    public $sub;

    /**
     * @JSA\Type("string")
     * @JSA\SerializedName("employee_id")
     * @var string
     */
    public $employeeId;

    /**
     * @JSA\Type("string")
     * @JSA\SerializedName("givenName")
     * @var string
     */
    public $givenName;

    /**
     * @JSA\Type("string")
     * @JSA\SerializedName("family_name")
     * @var string
     */
    public $familyName;

    /**
     * @JSA\Type("string")
     * @var string
     */
    public $name;

    /**
     * @JSA\Type("array<string>")
     * @var string[]
     */
    public $role;

}