<?php
/**
 * Создано: 05.02.2018 Яковенко Никита <nyakovenko@htc-cs.ru>
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user_status")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserStatusRepository")
 */
class UserStatus
{
    const RESTING = 1;
    const READY_TO_FIGHT = 2;
    const FIGHTING = 3;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $lastChangeTime;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastChangeTime(): \DateTime
    {
        return $this->lastChangeTime;
    }

    /**
     * @param \DateTime $lastChangeTime
     */
    public function setLastChangeTime(\DateTime $lastChangeTime): self
    {
        $this->lastChangeTime = $lastChangeTime;

        return $this;
    }
}