<?php

namespace App\Entity;

use App\Enum\DuelStatus;
use App\Repository\DuelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DuelRepository::class)]
#[ORM\Table(name: 'duels')]
#[ORM\UniqueConstraint(name: 'uniq_duel_room_code', fields: ['roomCode'])]
class Duel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 12)]
    private string $roomCode = '';

    #[ORM\Column(type: 'string', length: 16, enumType: DuelStatus::class)]
    private DuelStatus $status = DuelStatus::Pending;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(name: 'duration_s', type: Types::INTEGER)]
    private int $durationSeconds = 300;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $revealAt = null;

    /**
     * @var Collection<int, DuelParticipant>
     */
    #[ORM\OneToMany(mappedBy: 'duel', targetEntity: DuelParticipant::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $participants;

    public function __construct(string $roomCode = '')
    {
        if ($roomCode !== '') {
            $this->roomCode = $roomCode;
        }
        $this->createdAt = new \DateTimeImmutable();
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoomCode(): string
    {
        return $this->roomCode;
    }

    public function setRoomCode(string $roomCode): static
    {
        $this->roomCode = $roomCode;

        return $this;
    }

    public function getStatus(): DuelStatus
    {
        return $this->status;
    }

    public function setStatus(DuelStatus $status): static
    {
        $this->status = $status;

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

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
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

    public function getDurationSeconds(): int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(int $durationSeconds): static
    {
        $this->durationSeconds = $durationSeconds;

        return $this;
    }

    public function getRevealAt(): ?\DateTimeImmutable
    {
        return $this->revealAt;
    }

    public function setRevealAt(?\DateTimeImmutable $revealAt): static
    {
        $this->revealAt = $revealAt;

        return $this;
    }

    /**
     * @return Collection<int, DuelParticipant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(DuelParticipant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setDuel($this);
        }

        return $this;
    }

    public function removeParticipant(DuelParticipant $participant): static
    {
        if ($this->participants->removeElement($participant) && $participant->getDuel() === $this) {
            $participant->setDuel(null);
        }

        return $this;
    }
}
