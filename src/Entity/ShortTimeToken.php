<?php

namespace App\Entity;

use App\Repository\ShortTimeTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShortTimeTokenRepository::class)
 */
class ShortTimeToken
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
    private $token;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $refreshToken;

    /**
     * @ORM\Column(type="datetime")
     */
    private $tokenExpiration;

    /**
     * @ORM\Column(type="datetime")
     */
    private $refreshTokenExpiration;

    /**
     * @ORM\ManyToOne(targetEntity=ApiKey::class, inversedBy="shortTimeToken")
     */
    private $apiKey;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getTokenExpiration(): ?\DateTimeInterface
    {
        return $this->tokenExpiration;
    }

    public function setTokenExpiration(\DateTimeInterface $tokenExpiration): self
    {
        $this->tokenExpiration = $tokenExpiration;

        return $this;
    }

    public function getRefreshTokenExpiration(): ?\DateTimeInterface
    {
        return $this->refreshTokenExpiration;
    }

    public function setRefreshTokenExpiration(\DateTimeInterface $refreshTokenExpiration): self
    {
        $this->refreshTokenExpiration = $refreshTokenExpiration;

        return $this;
    }

    public function getApiKey(): ?ApiKey
    {
        return $this->apiKey;
    }

    public function setApiKey(?ApiKey $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }
}
