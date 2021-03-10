<?php
/**
 * Создано: 29.01.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Controller;

use AppBundle\DTO\Request\ChangeSkillRequest;
use AppBundle\DTO\Request\SaveProfileRequest;
use AppBundle\DTO\Request\BeginRoundRequest;
use AppBundle\DTO\Request\SendMessageRequest;
use AppBundle\Entity\UserStatus;
use AppBundle\Exception\BadRequestException;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use FOS\RestBundle\Controller\Annotations as FRA;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends FOSRestController
{
    /**
     * @FRA\Get(path="/authorize")
     * @FRA\View()
     */
    public function authorizeAction()
    {
        $user = $this->getUserService()->getUserByToken($this->getToken());

        return $user;
    }

    /**
     * @FRA\Post(path="/saveProfile")
     * @FRA\View()
     *
     * @ParamConverter("saveProfileRequest", converter="fos_rest.request_body")
     *
     * @param SaveProfileRequest $saveProfileRequest
     * @param ConstraintViolationListInterface $validationErrors
     */
    public function saveProfileAction(SaveProfileRequest $saveProfileRequest, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            throw new BadRequestException($validationErrors);
        }

        $user = $this->getUserService()->editUserByToken($this->getToken(), $saveProfileRequest);

        return $user;
    }

    /**
     * @FRA\Post(path="/sendMessage")
     * @FRA\View()
     *
     * @ParamConverter("sendMessageRequest", converter="fos_rest.request_body")
     *
     * @param SendMessageRequest $sendMessageRequest
     * @param ConstraintViolationListInterface $validationErrors
     */
    public function sendMessageAction(SendMessageRequest $sendMessageRequest, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            throw new BadRequestException($validationErrors);
        }

        $user = $this->getUserService()->getUserByToken($this->getToken());

        $result = $this->getChatService()->sendChatMessage($user, $sendMessageRequest);

        return $result;
    }

    /**
     * @FRA\Get(path="/readyToFight")
     * @FRA\View()
     */
    public function readyToFightAction()
    {
        $user = $this->getUserService()->getUserByToken($this->getToken());

        $this->getUserService()->changeStatus($user, UserStatus::READY_TO_FIGHT);

        $this->getBattleService()->tryStartBattle();

        return true;
    }

    /**
     * @FRA\Post(path="/changeSkill")
     * @FRA\View()
     *
     * @ParamConverter("changeSkillRequest", converter="fos_rest.request_body")
     *
     * @param ChangeSkillRequest $changeSkillRequest
     * @param ConstraintViolationListInterface $validationErrors
     */
    public function changeSkillAction(ChangeSkillRequest $changeSkillRequest, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            throw new BadRequestException($validationErrors);
        }

        $user = $this->getUserService()->getUserByToken($this->getToken());

        $this->getBattleService()->changeSkill($user, $changeSkillRequest);

        return true;
    }

    /**
     * @FRA\Post(path="/beginRound")
     * @FRA\View()
     *
     * @ParamConverter("beginRoundRequest", converter="fos_rest.request_body")
     *
     * @param BeginRoundRequest $beginRoundRequest
     * @param ConstraintViolationListInterface $validationErrors
     */
    public function beginRoundAction(BeginRoundRequest $beginRoundRequest, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            throw new BadRequestException($validationErrors);
        }

        $user = $this->getUserService()->getUserByToken($this->getToken());

        $this->getBattleService()->beginRound($user, $beginRoundRequest);

        return true;
    }

    /**
     * @FRA\Get(path="/getRoundResult")
     * @FRA\View()
     */
    public function getRoundResultAction()
    {
        $user = $this->getUserService()->getUserByToken($this->getToken());

        if ($this->getBattleService()->isOpponentGone($user)) {
            $this->getBattleService()->giveTechnicalWin($user);
        }

        return true;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        $request = Request::createFromGlobals();
        $token = $request->headers->get('authorization');

        return $token;
    }

    /**
     * @return \AppBundle\Service\UserService
     */
    public function getUserService()
    {
        return $this->get('app.service.user_service');
    }

    /**
     * @return \AppBundle\Service\ChatService
     */
    public function getChatService()
    {
        return $this->get('app.service.chat_service');
    }

    /**
     * @return \AppBundle\Service\Publisher\CrossbarService
     */
    public function getCrossbarService()
    {
        return $this->get('app.service.crossbar_service');
    }

    /**
     * @return \AppBundle\Service\BattleService
     */
    public function getBattleService()
    {
        return $this->get('app.service.battle_service');
    }

    /**
     * @return \AppBundle\Service\RatingService
     */
    public function getRatingService()
    {
        return $this->get('app.service.rating_service');
    }
}