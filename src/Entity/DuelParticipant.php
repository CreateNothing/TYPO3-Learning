<?php

namespace App\Entity;

use App\Repository\DuelParticipantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DuelParticipantRepository::class)]
#[ORM\Table(name: 'duel_participants')]
#[ORM\UniqueConstraint(name: 'uniq_duel_participant', columns: ['duel_id', 'user_id'])]
#[ORM\Index(name: 'idx_duel_participant_duel', columns: ['duel_id'])]
class DuelParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Duel::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(name: 'duel_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Duel $duel = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(name: 'final_score', type: Types::INTEGER, nullable: true)]
    private ?int $finalScore = null;

    #[ORM\Column(name: 'rank_after_reveal', type: Types::SMALLINT, nullable: true)]
    private ?int $rankAfterReveal = null;

    #[ORM\Column(name: 'bonus_points', type: Types::INTEGER, nullable: true)]
    private ?int $bonusPoints = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDuel(): ?Duel
    {
        return $this->duel;
    }

    public function setDuel(?Duel $duel): static
    {
        $this->duel = $duel;

        return $this;
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

    public function getFinalScore(): ?int
    {
        return $this->finalScore;
    }

    public function setFinalScore(?int $finalScore): static
    {
        $this->finalScore = $finalScore;

        return $this;
    }

    public function getRankAfterReveal(): ?int
    {
        return $this->rankAfterReveal;
    }

    public function setRankAfterReveal(?int $rankAfterReveal): static
    {
        $this->rankAfterReveal = $rankAfterReveal;

        return $this;
    }

    public function getBonusPoints(): ?int
    {
        return $this->bonusPoints;
    }

    public function setBonusPoints(?int $bonusPoints): static
    {
        $this->bonusPoints = $bonusPoints;

        return $this;
    }
}
