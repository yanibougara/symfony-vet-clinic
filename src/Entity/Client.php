<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ASSISTANT') or is_granted('ROLE_VETERINARIAN') or is_granted('ROLE_DIRECTOR')",
            normalizationContext: ['groups' => ['client:read']],
        ),
        new Get(
            security: "is_granted('ROLE_ASSISTANT') or is_granted('ROLE_VETERINARIAN') or is_granted('ROLE_DIRECTOR')",
            normalizationContext: ['groups' => ['client:read', 'client:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_ASSISTANT')",
            denormalizationContext: ['groups' => ['client:write']],
        ),
        new Put(
            security: "is_granted('ROLE_ASSISTANT')",
            denormalizationContext: ['groups' => ['client:update']],
        ),
    ],
    normalizationContext: ['groups' => ['client:read'], 'enable_max_depth' => true],
    denormalizationContext: ['groups' => ['client:write'], 'enable_max_depth' => true],
)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['client:read', 'animal:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write', 'client:update', 'animal:read'])]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write', 'client:update', 'animal:read'])]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write', 'client:update'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['client:read', 'client:write', 'client:update'])]
    #[Assert\Regex(pattern: '/^[0-9]{10}$/')]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['client:read', 'client:write', 'client:update'])]
    private ?string $address = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Animal::class, orphanRemoval: true)]
    #[Groups(['client:detail'])]
    #[MaxDepth(1)]
    private Collection $animals;

    public function __construct()
    {
        $this->animals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Animal>
     */
    public function getAnimals(): Collection
    {
        return $this->animals;
    }

    public function addAnimal(Animal $animal): static
    {
        if (!$this->animals->contains($animal)) {
            $this->animals->add($animal);
            $animal->setOwner($this);
        }

        return $this;
    }

    public function removeAnimal(Animal $animal): static
    {
        if ($this->animals->removeElement($animal)) {
            // set the owning side to null (unless already changed)
            if ($animal->getOwner() === $this) {
                $animal->setOwner(null);
            }
        }

        return $this;
    }
}