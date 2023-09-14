<?php

namespace App\Entity;

use App\Repository\RateLimitRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RateLimitRepository::class)
 */
class RateLimit
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ipAddress;

    /**
     * @ORM\Column(type="integer")
     */
    private $requestCount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastRequestAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getRequestCount(): ?int
    {
        return $this->requestCount;
    }

    public function setRequestCount(int $requestCount): self
    {
        $this->requestCount = $requestCount;

        return $this;
    }

    public function getLastRequestAt(): ?\DateTimeInterface
    {
        return $this->lastRequestAt;
    }

    public function setLastRequestAt(\DateTimeInterface $lastRequestAt): self
    {
        $this->lastRequestAt = $lastRequestAt;

        return $this;
    }
}
