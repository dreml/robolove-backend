<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User
{
    const PLAYER_USER_ROLE = 1;
    const SPECTATOR_USER_ROLE = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nickname;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $robotModel;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $robotColor;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $currentPoints;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $points;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $role;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $vip;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $wins;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $loses;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $lastFightTime;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $maxFightWaitingTime;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\UserStatus", mappedBy="user")
     */
    private $status;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getNickname(): string
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     */
    public function setNickname(string $nickname)
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * @return int
     */
    public function getRobotModel(): int
    {
        return $this->robotModel;
    }

    /**
     * @param int $robotModel
     */
    public function setRobotModel(int $robotModel)
    {
        $this->robotModel = $robotModel;

        return $this;
    }

    /**
     * @return string
     */
    public function getRobotColor(): string
    {
        return $this->robotColor;
    }

    /**
     * @param string $robotColor
     */
    public function setRobotColor(string $robotColor)
    {
        $this->robotColor = $robotColor;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPoints(): int
    {
        return $this->currentPoints;
    }

    /**
     * @param int $currentPoints
     */
    public function setCurrentPoints(int $currentPoints)
    {
        $this->currentPoints = $currentPoints;

        return $this;
    }

    /**
     * @return int
     */
    public function getPoints(): int
    {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints(int $points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * @return int
     */
    public function getRole(): int
    {
        return $this->role;
    }

    /**
     * @param int $role
     */
    public function setRole(int $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVip(): bool
    {
        return $this->vip;
    }

    /**
     * @param bool $vip
     */
    public function setVip(bool $vip)
    {
        $this->vip = $vip;

        return $this;
    }

    /**
     * @return int
     */
    public function getWins(): int
    {
        return $this->wins;
    }

    /**
     * @param int $wins
     */
    public function setWins(int $wins)
    {
        $this->wins = $wins;

        return $this;
    }

    /**
     * @return int
     */
    public function getLoses(): int
    {
        return $this->loses;
    }

    /**
     * @param int $loses
     */
    public function setLoses(int $loses)
    {
        $this->loses = $loses;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastFightTime(): \DateTime
    {
        return $this->lastFightTime;
    }

    /**
     * @param \DateTime $lastFightTime
     */
    public function setLastFightTime(\DateTime $lastFightTime)
    {
        $this->lastFightTime = $lastFightTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxFightWaitingTime(): int
    {
        return $this->maxFightWaitingTime;
    }

    /**
     * @param int $maxFightWaitingTime
     */
    public function setMaxFightWaitingTime(int $maxFightWaitingTime): self
    {
        $this->maxFightWaitingTime = $maxFightWaitingTime;

        return $this;
    }

    /**
     * @return UserStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

}
