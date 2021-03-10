<?php
/**
 * Создано: 06.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Service;

use AppBundle\DTO\Request\SendMessageRequest;
use AppBundle\Entity\Chat;
use AppBundle\Entity\Rating;
use AppBundle\Entity\User;
use AppBundle\Repository\ChatRepository;
use AppBundle\Repository\RatingRepository;
use AppBundle\Repository\UserRepository;
use AppBundle\Service\Publisher\PublisherInterface;
use Doctrine\ORM\EntityManager;

class RatingService
{
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var RatingRepository
     */
    private $ratingRepository;

    /**
     * RatingService constructor.
     *
     * @param EntityManager $em
     * @param PublisherInterface $publisher
     * @param UserService $userService
     */
    public function __construct(
        EntityManager $em,
        PublisherInterface $publisher,
        UserService $userService
    ) {
        $this->publisher = $publisher;
        $this->userService = $userService;
        $this->ratingRepository = $em->getRepository(Rating::class);
        $this->userRepository = $em->getRepository(User::class);
    }

    public function calculate()
    {
        $ratingInfo = [];
        $users = $this->userRepository->findBy([], ['points' => 'desc']);

        foreach ($users as $key => $user) {
            $userPosition = $key + 1;
            /** @var Rating $userRatingData */
            $userRatingData = $this->ratingRepository->getRatingByUser($user);

            if (is_null($userRatingData)) {
                $userRatingData = new Rating();
                $userRatingData
                    ->setUser($user)
                    ->setPosition($userPosition);
            } elseif ($userRatingData->getPosition() != $userPosition) {
                $userRatingData->setPosition($userPosition);
            }

            $this->ratingRepository->save($userRatingData);

            $ratingInfo[] = [
                'code'       => $user->getLogin(),
                'name'       => $user->getName(),
                'nickname'   => $user->getNickname(),
                'robotModel' => $user->getRobotModel(),
                'robotColor' => $user->getRobotColor(),
                'points'     => $user->getPoints(),
                'wins'       => $user->getWins(),
                'loses'      => $user->getLoses(),
                'rating'     => $userRatingData->getPosition(),
            ];
        }

        $this->publisher->publish(
            [
                'rating' => $ratingInfo,
            ],
            $this->publisher::LOBBY_TOPIC_NAME,
            $this->publisher::RATING_MESSAGE_TYPE
        );
    }
}