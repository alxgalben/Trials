<?php

namespace App\Entity;

use App\Repository\ApiKeyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApiKeyRepository::class)
 */
class ApiKey
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $apiKey;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expirationDate;

    /**
     * @ORM\OneToMany(targetEntity=ShortTimeToken::class, mappedBy="apiKey")
     */
    private $shortTimeToken;

    public function __construct()
    {
        $this->shortTimeToken = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * @return Collection<int, ShortTimeToken>
     */
    public function getShortTimeToken(): Collection
    {
        return $this->shortTimeToken;
    }

    public function addShortTimeToken(ShortTimeToken $shortTimeToken): self
    {
        if (!$this->shortTimeToken->contains($shortTimeToken)) {
            $this->shortTimeToken[] = $shortTimeToken;
            $shortTimeToken->setApiKey($this);
        }

        return $this;
    }

    public function removeShortTimeToken(ShortTimeToken $shortTimeToken): self
    {
        if ($this->shortTimeToken->removeElement($shortTimeToken)) {
            // set the owning side to null (unless already changed)
            if ($shortTimeToken->getApiKey() === $this) {
                $shortTimeToken->setApiKey(null);
            }
        }

        return $this;
    }
}
