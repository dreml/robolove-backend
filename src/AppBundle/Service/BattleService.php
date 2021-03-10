<?php
/**
 * Создано: 06.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Service;

use AppBundle\DTO\Request\ChangeSkillRequest;
use AppBundle\DTO\Request\BeginRoundRequest;
use AppBundle\Entity\Chat;
use AppBundle\Entity\Match;
use AppBundle\Entity\Rating;
use AppBundle\Entity\User;
use AppBundle\Entity\UserStatus;
use AppBundle\Repository\MatchRepository;
use AppBundle\Repository\RatingRepository;
use AppBundle\Repository\UserRepository;
use AppBundle\Repository\UserStatusRepository;
use AppBundle\Service\Publisher\PublisherInterface;
use Doctrine\ORM\EntityManager;

class BattleService
{
    const EARLY_VICTORY_ROUNDS = 2;
    const MAX_ROUNDS = 3;

    const WIN_REWARD = 500;
    const LOSE_REWARD = 100;

    const ROCK_SKILL_ID = 'rock';
    const PAPER_SKILL_ID = 'paper';
    const SCISSORS_SKILL_ID = 'scissors';
    const UNSELECTED_SKILL_ID = 'unselected';

    public static $balanceMap = [
        self::ROCK_SKILL_ID       => self::SCISSORS_SKILL_ID,
        self::PAPER_SKILL_ID      => self::ROCK_SKILL_ID,
        self::SCISSORS_SKILL_ID   => self::PAPER_SKILL_ID,
        self::UNSELECTED_SKILL_ID => self::UNSELECTED_SKILL_ID,
    ];

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserStatusRepository
     */
    private $userStatusRepository;

    /**
     * @var MatchRepository
     */
    private $matchRepository;

    /**
     * @var RatingRepository
     */
    private $ratingRepository;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var RatingService
     */
    private $ratingService;

    /**
     * @var int
     */
    private $leaverCheckCooldown;

    /**
     * BattleService constructor.
     *
     * @param EntityManager $em
     * @param PublisherInterface $publisher
     * @param RatingService $ratingService
     */
    public function __construct(
        EntityManager $em,
        PublisherInterface $publisher,
        RatingService $ratingService,
        int $leaverCheckCooldown
    ) {
        $this->userRepository = $em->getRepository(User::class);
        $this->userStatusRepository = $em->getRepository(UserStatus::class);
        $this->matchRepository = $em->getRepository(Match::class);
        $this->ratingRepository = $em->getRepository(Rating::class);
        $this->publisher = $publisher;
        $this->ratingService = $ratingService;
        $this->leaverCheckCooldown = $leaverCheckCooldown;
    }

    /**
     * @return bool
     */
    public function tryStartBattle()
    {
        $usersReadyToFight = $this->userStatusRepository->findBy(['status' => UserStatus::READY_TO_FIGHT]);
        if (count($usersReadyToFight) >= 2) {
            [$firstPlayerKey, $secondPlayerKey] = array_rand($usersReadyToFight, 2);
            $this->beginBattle($usersReadyToFight[$firstPlayerKey]->getUser(), $usersReadyToFight[$secondPlayerKey]->getUser());
        }

        return true;
    }

    /**
     * @param User $firstPlayer
     * @param User $secondPlayer
     *
     * @return bool
     */
    public function beginBattle(User $firstPlayer, User $secondPlayer)
    {
        $matchCode = $this->generateMatchCode($firstPlayer, $secondPlayer);

        $matchInfoForFirstPlayer = new Match();
        $matchInfoForFirstPlayer
            ->setPlayer($firstPlayer)
            ->setCode($matchCode)
            ->setActive(true)
            ->setDateStarted(new \DateTime())
            ->setCurrentRound(0);
        $this->matchRepository->save($matchInfoForFirstPlayer);

        $matchInfoForSecondPlayer = new Match();
        $matchInfoForSecondPlayer
            ->setPlayer($secondPlayer)
            ->setCode($matchCode)
            ->setActive(true)
            ->setDateStarted(new \DateTime())
            ->setCurrentRound(0);
        $this->matchRepository->save($matchInfoForSecondPlayer);

        $firstPlayerRatingInfo = $this->ratingRepository->getRatingByUser($firstPlayer);
        $firstPlayerPosition = !is_null($firstPlayerRatingInfo) ? $firstPlayerRatingInfo->getPosition() : '';
        $secondPlayerRatingInfo = $this->ratingRepository->getRatingByUser($secondPlayer);
        $secondPlayerPosition = !is_null($secondPlayerRatingInfo) ? $secondPlayerRatingInfo->getPosition() : '';
        $message = [
            'battleTopicName' => $this->publisher::MATCH_TOPIC_PREFIX . $matchCode,
            'players'         => [
                [
                    'code'       => $firstPlayer->getLogin(),
                    'name'       => $firstPlayer->getName(),
                    'nickname'   => $firstPlayer->getNickname(),
                    'robotModel' => $firstPlayer->getRobotModel(),
                    'robotColor' => $firstPlayer->getRobotColor(),
                    'points'     => $firstPlayer->getPoints(),
                    'wins'       => $firstPlayer->getWins(),
                    'loses'      => $firstPlayer->getLoses(),
                    'rating'     => $firstPlayerPosition,
                ],
                [
                    'code'       => $secondPlayer->getLogin(),
                    'name'       => $secondPlayer->getName(),
                    'nickname'   => $secondPlayer->getNickname(),
                    'robotModel' => $secondPlayer->getRobotModel(),
                    'robotColor' => $secondPlayer->getRobotColor(),
                    'points'     => $secondPlayer->getPoints(),
                    'wins'       => $secondPlayer->getWins(),
                    'loses'      => $secondPlayer->getLoses(),
                    'rating'     => $secondPlayerPosition,
                ],
            ],
        ];

        $this->publisher->publish(
            $message,
            $this->publisher::LOBBY_TOPIC_NAME,
            $this->publisher::BEGINNING_OF_MATCH_MESSAGE_TYPE
        );

        $firstPlayerWaitingTime = time() - $firstPlayer->getStatus()->getLastChangeTime()->getTimestamp();
        if ($firstPlayerWaitingTime > $firstPlayer->getMaxFightWaitingTime()) {
            $firstPlayer->setMaxFightWaitingTime($firstPlayerWaitingTime);
            $this->userRepository->save($firstPlayer);
        }
        $secondPlayerWaitingTime = time() - $secondPlayer->getStatus()->getLastChangeTime()->getTimestamp();
        if ($secondPlayerWaitingTime > $secondPlayer->getMaxFightWaitingTime()) {
            $secondPlayer->setMaxFightWaitingTime($secondPlayerWaitingTime);
            $this->userRepository->save($secondPlayer);
        }

        $this->userStatusRepository->changeStatus($firstPlayer, UserStatus::FIGHTING);
        $this->userStatusRepository->changeStatus($secondPlayer, UserStatus::FIGHTING);

        return true;
    }

    /**
     * @param User $user
     * @param ChangeSkillRequest $changeSkillRequest
     *
     * @return bool
     */
    public function changeSkill(User $user, ChangeSkillRequest $changeSkillRequest)
    {
        /** @var Match $match */
        $match = $this->matchRepository->getActiveMatchByUser($user);

        $message = [
            'userCode'   => $user->getLogin(),
            'skillId'    => $changeSkillRequest->skillId,
            'skillValue' => $changeSkillRequest->skillValue,
        ];

        $this->publisher->publish(
            $message,
            $this->publisher::MATCH_TOPIC_PREFIX . $match->getCode(),
            $this->publisher::SKILL_CHANGE_MESSAGE_TYPE
        );

        return true;
    }

    /**
     * @param User $user
     * @param BeginRoundRequest $roundBeginRequest
     *
     * @return bool
     */
    public function saveRoundInfo(User $user, BeginRoundRequest $roundBeginRequest)
    {
        /** @var Match $match */
        $match = $this->matchRepository->getActiveMatchByUser($user);

        $skills = [];
        /** @var ChangeSkillRequest $skill */
        foreach ($roundBeginRequest->skills as $skill) {
            $skills[$skill->skillId] = $skill->skillValue;
        }

        $match->{'setRound' . $roundBeginRequest->roundNumber . 'Skills'}(json_encode($skills));

        $match->setCurrentRound($roundBeginRequest->roundNumber);

        $this->matchRepository->save($match);

        return true;
    }

    /**
     * @param User $user
     * @param BeginRoundRequest $beginRoundRequest
     *
     * @return bool
     */
    public function beginRound(User $user, BeginRoundRequest $beginRoundRequest)
    {
        $this->saveRoundInfo($user, $beginRoundRequest);

        $opponentMatchInfo = $this->matchRepository->getOpponentInfo($user);

        if (!empty($opponentMatchInfo->{'getRound' . $beginRoundRequest->roundNumber . 'Skills'}())) {
            /** @var Match $playerMatchInfo */
            $playerMatchInfo = $this->matchRepository->getActiveMatchByUser($user);
            $this->processRound($beginRoundRequest->roundNumber, $playerMatchInfo, $opponentMatchInfo);
        }

        return true;
    }

    /**
     * @param int $roundNumber
     * @param Match $firstPlayerMatchInfo
     * @param Match $secondPlayerMatchInfo
     *
     * @return bool
     */
    public function processRound(int $roundNumber, Match $firstPlayerMatchInfo, Match $secondPlayerMatchInfo)
    {
        $firstPlayerSkills = json_decode($firstPlayerMatchInfo->{'getRound' . $roundNumber . 'Skills'}(), true);
        $secondPlayerSkills = json_decode($secondPlayerMatchInfo->{'getRound' . $roundNumber . 'Skills'}(), true);

        $skillStats = [];
        $firstPlayerSkillWins = 0;
        $secondPlayerSkillWins = 0;
        foreach ($firstPlayerSkills as $skillId => $firstPlayerSkillValue) {
            if ($firstPlayerSkillValue == $secondPlayerSkills[$skillId]) {
                //ничья
                $skillStats[$skillId] = [
                    '1' => [
                        'id'     => $skillId,
                        'value'  => $firstPlayerSkillValue,
                        'winner' => false,
                    ],
                    '2' => [
                        'id'     => $skillId,
                        'value'  => $secondPlayerSkills[$skillId],
                        'winner' => false,
                    ],
                ];
            } else {
                if (
                    ($secondPlayerSkills[$skillId] == self::UNSELECTED_SKILL_ID)
                    || (self::$balanceMap[$firstPlayerSkillValue] == $secondPlayerSkills[$skillId])
                ) {
                    $firstPlayerSkillWins++;
                    $skillStats[$skillId] = [
                        '1' => [
                            'id'     => $skillId,
                            'value'  => $firstPlayerSkillValue,
                            'winner' => true,
                        ],
                        '2' => [
                            'id'     => $skillId,
                            'value'  => $secondPlayerSkills[$skillId],
                            'winner' => false,
                        ],
                    ];
                } elseif (
                    ($firstPlayerSkillValue == self::UNSELECTED_SKILL_ID)
                    || (self::$balanceMap[$secondPlayerSkills[$skillId]] == $firstPlayerSkillValue)
                ) {
                    $secondPlayerSkillWins++;
                    $skillStats[$skillId] = [
                        '1' => [
                            'id'     => $skillId,
                            'value'  => $firstPlayerSkillValue,
                            'winner' => false,
                        ],
                        '2' => [
                            'id'     => $skillId,
                            'value'  => $secondPlayerSkills[$skillId],
                            'winner' => true,
                        ],
                    ];
                }
            }
        }

        if ($firstPlayerSkillWins != $secondPlayerSkillWins) {
            if ($firstPlayerSkillWins > $secondPlayerSkillWins) {
                $firstPlayerMatchInfo->{'setRound' . $roundNumber . 'Win'}(true);
                $secondPlayerMatchInfo->{'setRound' . $roundNumber . 'Win'}(false);
            } else {
                $firstPlayerMatchInfo->{'setRound' . $roundNumber . 'Win'}(false);
                $secondPlayerMatchInfo->{'setRound' . $roundNumber . 'Win'}(true);
            }
        } else {
            $firstPlayerMatchInfo->{'setRound' . $roundNumber . 'Win'}(false);
            $secondPlayerMatchInfo->{'setRound' . $roundNumber . 'Win'}(false);
        }

        if ($roundNumber == self::EARLY_VICTORY_ROUNDS) {
            if ($firstPlayerMatchInfo->isRound1Win() && $firstPlayerMatchInfo->isRound2Win()) {
                $this->endMatch($roundNumber, $firstPlayerMatchInfo, $secondPlayerMatchInfo, true);
            }
            if ($secondPlayerMatchInfo->isRound1Win() && $secondPlayerMatchInfo->isRound2Win()) {
                $this->endMatch($roundNumber, $firstPlayerMatchInfo, $secondPlayerMatchInfo, true);
            }
        }

        if ($roundNumber == self::MAX_ROUNDS) {
            $this->endMatch($roundNumber, $firstPlayerMatchInfo, $secondPlayerMatchInfo);
        }

        $this->matchRepository->save($firstPlayerMatchInfo);
        $this->matchRepository->save($secondPlayerMatchInfo);

        $this->publisher->publish(
            $this->prepareRoundResultMessage($roundNumber, $firstPlayerMatchInfo, $secondPlayerMatchInfo, $skillStats),
            $this->publisher::MATCH_TOPIC_PREFIX . $firstPlayerMatchInfo->getCode(),
            $this->publisher::MATCH_RESULT_MESSAGE_TYPE
        );

        return true;
    }

    /**
     * @param int $roundNumber
     * @param Match $firstPlayerMatchInfo
     * @param Match $secondPlayerMatchInfo
     * @param bool $earlyVictory
     */
    public function endMatch(int $roundNumber, Match $firstPlayerMatchInfo, Match $secondPlayerMatchInfo, bool $earlyVictory = false)
    {
        $firstPlayerRoundWins = 0;
        $secondPlayerRoundWins = 0;
        $playedRounds = $earlyVictory ? self::EARLY_VICTORY_ROUNDS : self::MAX_ROUNDS;
        for ($round = 1; $round <= $playedRounds; $round++) {
            $firstPlayerRoundWins += $firstPlayerMatchInfo->{'isRound' . $roundNumber . 'Win'}();
            $secondPlayerRoundWins += $secondPlayerMatchInfo->{'isRound' . $roundNumber . 'Win'}();
        }

        $isFirstPlayerMatchWinner = false;
        $isSecondPlayerMatchWinner = false;
        if ($firstPlayerRoundWins != $secondPlayerRoundWins) {
            $isFirstPlayerMatchWinner = $firstPlayerRoundWins > $secondPlayerRoundWins;
            $isSecondPlayerMatchWinner = !$isFirstPlayerMatchWinner;
        }

        $this->scoring($firstPlayerMatchInfo->getPlayer(), $isFirstPlayerMatchWinner);
        $this->scoring($secondPlayerMatchInfo->getPlayer(), $isSecondPlayerMatchWinner);

        $firstPlayerMatchInfo
            ->setWinner($isFirstPlayerMatchWinner)
            ->setDateFinished(new \DateTime())
            ->setActive(false);
        $secondPlayerMatchInfo
            ->setWinner($isSecondPlayerMatchWinner)
            ->setDateFinished(new \DateTime())
            ->setActive(false);

        $this->matchRepository->save($firstPlayerMatchInfo);
        $this->matchRepository->save($secondPlayerMatchInfo);

        $this->publisher->publish(
            $this->prepareMatchResultMessage($firstPlayerMatchInfo, $secondPlayerMatchInfo),
            $this->publisher::MATCH_TOPIC_PREFIX . $firstPlayerMatchInfo->getCode(),
            $this->publisher::MATCH_RESULT_MESSAGE_TYPE
        );

        /** @var UserStatus $firstPlayerStatus */
        $firstPlayerStatus = $firstPlayerMatchInfo->getPlayer()->getStatus();
        $firstPlayerStatus
            ->setStatus(UserStatus::RESTING)
            ->setLastChangeTime(new \DateTime());
        $this->userStatusRepository->save($firstPlayerStatus);

        /** @var UserStatus $secondPlayerStatus */
        $secondPlayerStatus = $secondPlayerMatchInfo->getPlayer()->getStatus();
        $secondPlayerStatus
            ->setStatus(UserStatus::RESTING)
            ->setLastChangeTime(new \DateTime());
        $this->userStatusRepository->save($secondPlayerStatus);

        $this->ratingService->calculate();
    }

    /**
     * @param User $player
     * @param bool $winner
     *
     * @return User
     */
    public function scoring(User $player, bool $winner = false)
    {
        if ($winner) {
            $player
                ->setWins($player->getWins() + 1)
                ->setCurrentPoints($player->getCurrentPoints() + self::WIN_REWARD)
                ->setPoints($player->getPoints() + self::WIN_REWARD)
                ->setLastFightTime(new \DateTime());
        } else {
            $player
                ->setLoses($player->getLoses() + 1)
                ->setCurrentPoints($player->getCurrentPoints() + self::LOSE_REWARD)
                ->setPoints($player->getPoints() + self::LOSE_REWARD)
                ->setLastFightTime(new \DateTime());
        }

        $this->userRepository->save($player);

        return $player;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isOpponentGone(User $user)
    {
        /** @var Match $userMatchInfo */
        $userMatchInfo = $this->matchRepository->getActiveMatchByUser($user);

        if (!is_null($userMatchInfo)) {
            return (time() - $userMatchInfo->getDateStarted()->getTimestamp()) >= $this->leaverCheckCooldown;
        } else {
            return false;
        }
    }

    /**
     * @param int $roundNumber
     * @param Match $firstPlayerMatchInfo
     * @param Match $secondPlayerMatchInfo
     *
     * @return array
     */
    public function prepareRoundResultMessage(int $roundNumber, Match $firstPlayerMatchInfo, Match $secondPlayerMatchInfo, array $skillStats)
    {
        $firstPlayerSkillsInfo = [];
        $secondPlayerSkillsInfo = [];
        foreach ($skillStats as $id => $playersSkillStat) {
            $firstPlayerSkillStat = $playersSkillStat[1];
            $secondPlayerSkillStat = $playersSkillStat[2];

            $firstPlayerSkillsInfo[$id] = [
                'id'     => $id,
                'value'  => $firstPlayerSkillStat['value'],
                'winner' => $firstPlayerSkillStat['winner'],
            ];
            $secondPlayerSkillsInfo[$id] = [
                'id'     => $id,
                'value'  => $secondPlayerSkillStat['value'],
                'winner' => $secondPlayerSkillStat['winner'],
            ];
        }

        return [
            [
                'code'   => $firstPlayerMatchInfo->getPlayer()->getLogin(),
                'winner' => $firstPlayerMatchInfo->{'isRound' . $roundNumber . 'Win'}(),
                'skills' => $firstPlayerSkillsInfo,
            ],
            [
                'code'   => $secondPlayerMatchInfo->getPlayer()->getLogin(),
                'winner' => $secondPlayerMatchInfo->{'isRound' . $roundNumber . 'Win'}(),
                'skills' => $secondPlayerSkillsInfo,
            ],
        ];
    }

    /**
     * @param Match $firstPlayerMatchInfo
     * @param Match $secondPlayerMatchInfo
     *
     * @return array
     */
    public function prepareMatchResultMessage(Match $firstPlayerMatchInfo, Match $secondPlayerMatchInfo)
    {
        return [
            [
                'code'         => $firstPlayerMatchInfo->getPlayer()->getLogin(),
                'winner'       => $firstPlayerMatchInfo->isWinner(),
                'earnedPoints' => $firstPlayerMatchInfo->isWinner() ? self::WIN_REWARD : self::LOSE_REWARD,
            ],
            [
                'code'         => $secondPlayerMatchInfo->getPlayer()->getLogin(),
                'winner'       => $secondPlayerMatchInfo->isWinner(),
                'earnedPoints' => $secondPlayerMatchInfo->isWinner() ? self::WIN_REWARD : self::LOSE_REWARD,
            ],
        ];
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function giveTechnicalWin(User $user): bool
    {
        /** @var Match $userMatchInfo */
        $userMatchInfo = $this->matchRepository->getActiveMatchByUser($user);
        $opponentMatchInfo = $this->matchRepository->getOpponentInfo($user);
        if ($opponentMatchInfo->getCurrentRound() < $userMatchInfo->getCurrentRound()) {
            $userMatchInfo
                ->setDateFinished(new \DateTime())
                ->setActive(false)
                ->setWinner(true);
            $opponentMatchInfo
                ->setDateFinished(new \DateTime())
                ->setActive(false)
                ->setWinner(false);

            $this->scoring($user, true);
            $this->scoring($opponentMatchInfo->getPlayer(), false);

            $this->matchRepository->save($userMatchInfo);
            $this->matchRepository->save($opponentMatchInfo);

            /** @var UserStatus $firstPlayerStatus */
            $firstPlayerStatus = $userMatchInfo->getPlayer()->getStatus();
            $firstPlayerStatus
                ->setStatus(UserStatus::RESTING)
                ->setLastChangeTime(new \DateTime());
            $this->userStatusRepository->save($firstPlayerStatus);

            /** @var UserStatus $secondPlayerStatus */
            $secondPlayerStatus = $opponentMatchInfo->getPlayer()->getStatus();
            $secondPlayerStatus
                ->setStatus(UserStatus::RESTING)
                ->setLastChangeTime(new \DateTime());
            $this->userStatusRepository->save($secondPlayerStatus);
        }

        return true;
    }

    /**
     * @param User $firstPlayer
     * @param User $secondPlayer
     *
     * @return string
     */
    public function generateMatchCode(User $firstPlayer, User $secondPlayer)
    {
        $matchCode = $firstPlayer->getLogin() . '_' . $secondPlayer->getLogin() . '_' . time();

        return $matchCode;
    }
}