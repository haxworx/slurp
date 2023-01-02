<?php

namespace App\Entity;

use App\Repository\RobotSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Validator as CustomValidator;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RobotSettingsRepository::class)]
class RobotSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Hostname]
    #[ORM\Column(length: 8192)]
    private ?string $domainName = null;


    #[CustomValidator\IsScheme]
    #[ORM\Column(length: 16)]
    private ?string $scheme = null;

    #[ORM\Column]
    private ?bool $importSitemaps = null;

    #[Assert\Range(
        min: 1,
        max: 10,
        notInRangeMessage: 'Must be between {{ min }} and {{ max }}.',
    )]
    #[ORM\Column]
    private ?int $retryMax = null;

    #[ORM\Column]
    private ?bool $isRunning = false;

    #[ORM\Column(type: 'time', nullable: true)]
    private $startTime = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private $endTime = null;

    #[CustomValidator\IsUserAgent]
    #[ORM\Column(length: 255)]
    private ?string $userAgent = null;

    #[ORM\Column]
    private ?int $userId = null;

    #[Assert\Range(
        min: 1,
        max: 10,
        notInRangeMessage: 'Must be between {{ min }} and {{ max }}.',
    )]
    #[ORM\Column]
    private ?int $scanDelay = null;

    #[ORM\Column]
    private ?bool $hasError = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomainName(): ?string
    {
        return $this->domainName;
    }

    public function setDomainName(string $domainName): self
    {
        $this->domainName = $domainName;

        return $this;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setScheme(string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function isImportSitemaps(): ?bool
    {
        return $this->importSitemaps;
    }

    public function setImportSitemaps(bool $importSitemaps): self
    {
        $this->importSitemaps = $importSitemaps;

        return $this;
    }

    public function getRetryMax(): ?int
    {
        return $this->retryMax;
    }

    public function setRetryMax(int $retryMax): self
    {
        $this->retryMax = $retryMax;

        return $this;
    }

    public function IsRunning(): ?bool
    {
        return $this->isRunning;
    }

    public function setIsRunning(bool $isRunning): self
    {
        $this->isRunning = $isRunning;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getScanDelay(): ?int
    {
        return $this->scanDelay;
    }

    public function setScanDelay(int $scanDelay): self
    {
        $this->scanDelay = $scanDelay;

        return $this;
    }

    public function hasError(): ?bool
    {
        return $this->hasError;
    }

    public function setHasError(bool $hasError): self
    {
        $this->hasError = $hasError;

        return $this;
    }
}
