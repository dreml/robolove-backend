<?php
/**
 * Создано: 29.01.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class BadRequestException extends BadRequestHttpException
{
    /** @var ConstraintViolationListInterface */
    public $validationErrors;

    public function __construct(
        ConstraintViolationListInterface $validationErrors,
        $message = 'Некорректный запрос',
        $code = 0,
        \Throwable $previous = null
    ) {
        $this->validationErrors = $validationErrors;

        parent::__construct($message, $previous, $code);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}