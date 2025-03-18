<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['animal:read']],
        ),
        new Get(
            normalizationContext: ['groups' => ['animal:read', 'animal:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_ASSISTANT')",
            denormalizationContext: ['groups' => ['animal:write']],
        ),
        new Put(
            security: "is_granted('ROLE_ASSISTANT')",
            denormalizationContext: ['groups' => ['animal:update']],
        ),
    ],
    normalizationContext: ['groups' => ['animal:read'], 'enable_max_depth' => true],
    denormalizationContext: ['groups' => ['animal:write'], 'enable_max_depth' => true],
)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['animal:read', 'appointment:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['animal:read', 'animal:write', 'animal:update', 'appointment:read'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['animal:read', 'animal:write', 'animal:update', 'appointment:read'])]
    #[Assert\NotBlank]
    private ?string $species = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['animal:read', 'animal:write', 'animal:update', 'appointment:read'])]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['animal:read', 'animal:write', 'animal:update'])]
    private ?Media $photo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['animal:read', 'animal:write', 'animal:update'])]
    #[Assert\NotBlank]
    private ?Client $owner = null;

    #[ORM\OneToMany(mappedBy: 'animal', targetEntity: Appointment::class)]
    #[Groups(['animal:detail'])]
    #[MaxDepth(1)]
    private Collection $appointments;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSpecies(): ?string
    {
        return $this->species;
    }

    public function setSpecies(string $species): static
    {
        $this->species = $species;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getPhoto(): ?Media
    {
        return $this->photo;
    }

    public function setPhoto(?Media $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getOwner(): ?Client
    {
        return $this->owner;
    }

    public function setOwner(?Client $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setAnimal($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getAnimal() === $this) {
                $appointment->setAnimal(null);
            }
        }

        return $this;
    }
}