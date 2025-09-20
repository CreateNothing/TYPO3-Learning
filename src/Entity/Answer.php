<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
#[ORM\Table(name: 'answers')]
#[ORM\Index(name: 'idx_answers_session', columns: ['session_id'])]
#[ORM\Index(name: 'idx_answers_question', columns: ['question_id'])]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'answers')]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Session $session = null;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'answers')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Question $question = null;

    #[ORM\Column(name: 'user_answer', type: Types::JSON, nullable: true)]
    private mixed $userAnswer = null;

    #[ORM\Column(name: 'is_correct', type: Types::BOOLEAN)]
    private bool $isCorrect = false;

    #[ORM\Column(name: 'time_ms', type: Types::INTEGER, nullable: true)]
    private ?int $timeMs = null;

    #[ORM\Column(name: 'awarded_points', type: Types::INTEGER, nullable: true)]
    private ?int $awardedPoints = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getUserAnswer(): mixed
    {
        return $this->userAnswer;
    }

    public function setUserAnswer(mixed $userAnswer): static
    {
        $this->userAnswer = $userAnswer;

        return $this;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): static
    {
        $this->isCorrect = $isCorrect;

        return $this;
    }

    public function getTimeMs(): ?int
    {
        return $this->timeMs;
    }

    public function setTimeMs(?int $timeMs): static
    {
        $this->timeMs = $timeMs;

        return $this;
    }

    public function getAwardedPoints(): ?int
    {
        return $this->awardedPoints;
    }

    public function setAwardedPoints(?int $awardedPoints): static
    {
        $this->awardedPoints = $awardedPoints;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
