<?php

namespace App\Entity;

use App\Enum\SessionMode;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: 'sessions')]
#[ORM\Index(name: 'idx_sessions_user_mode', columns: ['user_id', 'mode'])]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 16, enumType: SessionMode::class)]
    private SessionMode $mode = SessionMode::Learn;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $score = 0;

    #[ORM\Column(name: 'streak_max', type: Types::INTEGER)]
    private int $streakMax = 0;

    #[ORM\Column(name: 'streak_current', type: Types::INTEGER)]
    private int $streakCurrent = 0;

    #[ORM\Column(name: 'total_time_ms', type: Types::INTEGER, nullable: true)]
    private ?int $totalTimeMs = null;

    /**
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(mappedBy: 'session', targetEntity: Answer::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $answers;

    public function __construct(?User $user = null, ?SessionMode $mode = null)
    {
        if ($user !== null) {
            $this->user = $user;
        }

        if ($mode !== null) {
            $this->mode = $mode;
        }

        $this->startedAt = new \DateTimeImmutable();
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMode(): SessionMode
    {
        return $this->mode;
    }

    public function setMode(SessionMode $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getStreakMax(): int
    {
        return $this->streakMax;
    }

    public function setStreakMax(int $streakMax): static
    {
        $this->streakMax = $streakMax;

        return $this;
    }

    public function getStreakCurrent(): int
    {
        return $this->streakCurrent;
    }

    public function setStreakCurrent(int $streakCurrent): static
    {
        $this->streakCurrent = $streakCurrent;

        return $this;
    }

    public function getTotalTimeMs(): ?int
    {
        return $this->totalTimeMs;
    }

    public function setTotalTimeMs(?int $totalTimeMs): static
    {
        $this->totalTimeMs = $totalTimeMs;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setSession($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if ($this->answers->removeElement($answer) && $answer->getSession() === $this) {
            $answer->setSession(null);
        }

        return $this;
    }
}
