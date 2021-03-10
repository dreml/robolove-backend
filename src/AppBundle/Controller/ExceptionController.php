<?php
/**
 * Создано: 01.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Controller;

use AppBundle\Exception\BadRequestException;
use FOS\RestBundle\Controller\ExceptionController as FOSExceptionController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ExceptionController extends FOSExceptionController
{
    /**
     * @param Request $request
     * @param \Exception|\Throwable $exception
     * @param DebugLoggerInterface|null $logger
     *
     * @return View|Response
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        if ($exception instanceof BadRequestException) {
            return new Response($exception->getMessage(), $exception->getCode());
        }

        return parent::showAction($request, $exception, $logger);
    }
}