<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Match
 *
 * @ORM\Table(name="match_info")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MatchRepository")
 */
class Match
{
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
     * @ORM\Column(type="string", name="match_code")
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="player", referencedColumnName="id")
     */
    private $player;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $currentRound;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="round_1_skills", nullable=true)
     */
    private $round1Skills;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="round_2_skills", nullable=true)
     */
    private $round2Skills;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="round_3_skills", nullable=true)
     */
    private $round3Skills;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="round_1_win", nullable=true)
     */
    private $round1Win;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="round_2_win", nullable=true)
     */
    private $round2Win;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="round_3_win", nullable=true)
     */
    private $round3Win;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $winner;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $dateStarted;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateFinished;

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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getPlayer()
    {
        return $this->player;
    }

    public function setPlayer($player)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentRound(): int
    {
        return $this->currentRound;
    }

    /**
     * @param int $currentRound
     */
    public function setCurrentRound(int $currentRound): self
    {
        $this->currentRound = $currentRound;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getRound1Skills(): ?string
    {
        return $this->round1Skills;
    }

    /**
     * @param string $round1Skills
     */
    public function setRound1Skills(string $round1Skills)
    {
        $this->round1Skills = $round1Skills;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getRound2Skills(): ?string
    {
        return $this->round2Skills;
    }

    /**
     * @param string $round2Skills
     */
    public function setRound2Skills(string $round2Skills)
    {
        $this->round2Skills = $round2Skills;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getRound3Skills(): ?string
    {
        return $this->round3Skills;
    }

    /**
     * @param string $round3Skills
     */
    public function setRound3Skills(string $round3Skills)
    {
        $this->round3Skills = $round3Skills;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRound1Win(): bool
    {
        return $this->round1Win;
    }

    /**
     * @param bool $round1Win
     */
    public function setRound1Win(bool $round1Win)
    {
        $this->round1Win = $round1Win;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRound2Win(): bool
    {
        return $this->round2Win;
    }

    /**
     * @param bool $round2Win
     */
    public function setRound2Win(bool $round2Win)
    {
        $this->round2Win = $round2Win;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRound3Win(): bool
    {
        return $this->round3Win;
    }

    /**
     * @param bool $round3Win
     */
    public function setRound3Win(bool $round3Win)
    {
        $this->round3Win = $round3Win;

        return $this;
    }

    /**
     * @return bool
     */
    public function isWinner(): bool
    {
        return $this->winner;
    }

    /**
     * @param bool $winner
     */
    public function setWinner(bool $winner)
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateStarted(): \DateTime
    {
        return $this->dateStarted;
    }

    /**
     * @param \DateTime $dateStarted
     */
    public function setDateStarted(\DateTime $dateStarted)
    {
        $this->dateStarted = $dateStarted;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateFinished(): \DateTime
    {
        return $this->dateFinished;
    }

    /**
     * @param \DateTime $dateFinished
     */
    public function setDateFinished(\DateTime $dateFinished)
    {
        $this->dateFinished = $dateFinished;

        return $this;
    }

}
