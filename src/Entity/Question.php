<?php

namespace App\Entity;

use App\Enum\QuestionOrigin;
use App\Enum\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'questions')]
#[ORM\Index(name: 'idx_questions_syllabus', columns: ['syllabus_item_id'])]
#[ORM\Index(name: 'idx_questions_type', columns: ['type'])]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 16, enumType: QuestionType::class)]
    private QuestionType $type = QuestionType::MultipleChoice;

    #[ORM\ManyToOne(targetEntity: SyllabusItem::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(name: 'syllabus_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?SyllabusItem $syllabusItem = null;

    #[ORM\Column(length: 32)]
    private string $difficulty = 'medium';

    #[ORM\Column(type: Types::TEXT)]
    private string $prompt;

    /**
     * @var array<int, string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $choices = null;

    /**
     * @var mixed|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private mixed $correct = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $solution = null;

    /**
     * @var list<int>
     */
    #[ORM\Column(name: 'source_chunk_ids', type: Types::JSON)]
    private array $sourceChunkIds = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'string', length: 16, enumType: QuestionOrigin::class)]
    private QuestionOrigin $createdBy = QuestionOrigin::Manual;

    /**
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class, orphanRemoval: true)]
    private Collection $answers;

    public function __construct(?QuestionType $type = null, ?QuestionOrigin $origin = null)
    {
        if ($type !== null) {
            $this->type = $type;
        }

        if ($origin !== null) {
            $this->createdBy = $origin;
        }

        $this->createdAt = new \DateTimeImmutable();
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): QuestionType
    {
        return $this->type;
    }

    public function setType(QuestionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSyllabusItem(): ?SyllabusItem
    {
        return $this->syllabusItem;
    }

    public function setSyllabusItem(?SyllabusItem $syllabusItem): static
    {
        $this->syllabusItem = $syllabusItem;

        return $this;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): static
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function getChoices(): ?array
    {
        return $this->choices;
    }

    public function setChoices(?array $choices): static
    {
        $this->choices = $choices;

        return $this;
    }

    public function getCorrect(): mixed
    {
        return $this->correct;
    }

    public function setCorrect(mixed $correct): static
    {
        $this->correct = $correct;

        return $this;
    }

    public function getSolution(): ?string
    {
        return $this->solution;
    }

    public function setSolution(?string $solution): static
    {
        $this->solution = $solution;

        return $this;
    }

    /**
     * @return list<int>
     */
    public function getSourceChunkIds(): array
    {
        return $this->sourceChunkIds;
    }

    /**
     * @param list<int> $sourceChunkIds
     */
    public function setSourceChunkIds(array $sourceChunkIds): static
    {
        $this->sourceChunkIds = $sourceChunkIds;

        return $this;
    }

    public function addSourceChunkId(int $chunkId): static
    {
        if (!in_array($chunkId, $this->sourceChunkIds, true)) {
            $this->sourceChunkIds[] = $chunkId;
        }

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

    public function getCreatedBy(): QuestionOrigin
    {
        return $this->createdBy;
    }

    public function setCreatedBy(QuestionOrigin $createdBy): static
    {
        $this->createdBy = $createdBy;

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
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if ($this->answers->removeElement($answer) && $answer->getQuestion() === $this) {
            $answer->setQuestion(null);
        }

        return $this;
    }
}
