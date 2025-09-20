<?php

namespace App\Entity;

use App\Repository\DocChunkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocChunkRepository::class)]
#[ORM\Table(name: 'doc_chunks')]
class DocChunk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $sourceRepo = null;

    #[ORM\Column(length: 255)]
    private ?string $docPath = null;

    #[ORM\Column(length: 255)]
    private ?string $version = null;

    #[ORM\Column(length: 16)]
    private ?string $lang = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $license = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $anchor = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contentMd = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $embeddingRef = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $payload = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceRepo(): ?string
    {
        return $this->sourceRepo;
    }

    public function setSourceRepo(string $sourceRepo): static
    {
        $this->sourceRepo = $sourceRepo;

        return $this;
    }

    public function getDocPath(): ?string
    {
        return $this->docPath;
    }

    public function setDocPath(string $docPath): static
    {
        $this->docPath = $docPath;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): static
    {
        $this->lang = $lang;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(?string $license): static
    {
        $this->license = $license;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAnchor(): ?string
    {
        return $this->anchor;
    }

    public function setAnchor(?string $anchor): static
    {
        $this->anchor = $anchor;

        return $this;
    }

    public function getContentMd(): ?string
    {
        return $this->contentMd;
    }

    public function setContentMd(string $contentMd): static
    {
        $this->contentMd = $contentMd;

        return $this;
    }

    public function getEmbeddingRef(): ?string
    {
        return $this->embeddingRef;
    }

    public function setEmbeddingRef(?string $embeddingRef): static
    {
        $this->embeddingRef = $embeddingRef;

        return $this;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
