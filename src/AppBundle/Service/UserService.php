<?php
/**
 * Создано: 01.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Service;

use AppBundle\DTO\Request\SaveProfileRequest;
use AppBundle\Entity\User;
use AppBundle\Entity\UserStatus;
use AppBundle\Repository\UserRepository;
use AppBundle\Repository\UserStatusRepository;
use AppBundle\Service\Proxy\YabtService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Simple\MemcachedCache;

class UserService
{
    public $statusTransactionMap = [
        UserStatus::RESTING        => UserStatus::READY_TO_FIGHT,
        UserStatus::READY_TO_FIGHT => UserStatus::FIGHTING,
        UserStatus::FIGHTING       => UserStatus::RESTING,
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
     * @var YabtService
     */
    private $yabtService;

    /**
     * @var int
     */
    private $battleCooldown;
    /**
     * @var int
     */
    private $cacheTtl;

    /**
     * UserService constructor.
     *
     * @param EntityManager $em
     * @param YabtService $yabtService
     * @param int $battleCooldown
     * @param string $cacheHost
     * @param int $cacheTtl
     *
     * @throws \ErrorException
     */
    public function __construct(
        EntityManager $em,
        YabtService $yabtService,
        int $battleCooldown,
        string $cacheHost,
        int $cacheTtl
    ) {
        $this->userRepository = $em->getRepository(User::class);
        $this->userStatusRepository = $em->getRepository(UserStatus::class);
        $this->yabtService = $yabtService;
        $this->battleCooldown = $battleCooldown;

        $client = MemcachedAdapter::createConnection($cacheHost);
        $this->cache = new MemcachedCache($client);
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @param string $token
     *
     * @return User|null|object
     */
    public function getUserByToken(string $token)
    {
        if ($this->cache->has($token)) {
            $userInfo = $this->cache->get($token);
        } else {
            $userInfo = $this->yabtService->getUserInfo($token);
            $this->cache->set($token, $userInfo, $this->cacheTtl);
        }

        $user = $this->userRepository->findOneBy(['login' => $userInfo->sub]);

        if (is_null($user)) {
            $user = $this->userRepository->createUser($userInfo);
        }

        return $user;
    }

    /**
     * @param string $token
     * @param SaveProfileRequest $profile
     *
     * @return User
     */
    public function editUserByToken(string $token, SaveProfileRequest $profile)
    {
        $user = $this->getUserByToken($token);

        $user
            ->setNickname($profile->nickname)
            ->setRobotModel($profile->robotModel)
            ->setRobotColor($profile->robotColor);

        return $this->userRepository->save($user);
    }

    /**
     * @param User $user
     * @param $cost
     *
     * @return bool
     */
    public function canPayForService(User $user, $cost)
    {
        return $user->getCurrentPoints() >= $cost;
    }

    /**
     * @param User $user
     * @param $cost
     *
     * @return User
     */
    public function payForService(User $user, $cost)
    {
        $user->setCurrentPoints($user->getCurrentPoints() - $cost);

        return $this->userRepository->save($user);
    }

    /**
     * @param User $user
     * @param int $status
     *
     * @return bool
     */
    public function changeStatus(User $user, int $status)
    {
        $canChangeStatus = true;
        $currentUserStatus = $user->getStatus()->getStatus();

        if ($this->statusTransactionMap[$currentUserStatus] == $status) {
            if ($currentUserStatus == UserStatus::RESTING) {
                if (!$this->canFight($user)) {
                    $canChangeStatus = false;
                }
            }
        } else {
            $canChangeStatus = false;
        }

        if ($canChangeStatus) {
            $this->userStatusRepository->changeStatus($user, $status);
        } else {
            throw new \Exception('Невозможно изменить статус');
        }

        return true;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canFight(User $user)
    {
        return (time() - $user->getLastFightTime()->getTimestamp()) >= $this->battleCooldown;
    }
}