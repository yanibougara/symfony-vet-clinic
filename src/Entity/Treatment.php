<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\TreatmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TreatmentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_VETERINARIAN') or is_granted('ROLE_ASSISTANT') or is_granted('ROLE_DIRECTOR')",
            normalizationContext: ['groups' => ['treatment:read']],
        ),
        new Get(
            security: "is_granted('ROLE_VETERINARIAN') or is_granted('ROLE_ASSISTANT') or is_granted('ROLE_DIRECTOR')",
            normalizationContext: ['groups' => ['treatment:read', 'treatment:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_VETERINARIAN')",
            denormalizationContext: ['groups' => ['treatment:write']],
        ),
        new Put(
            security: "is_granted('ROLE_VETERINARIAN')",
            denormalizationContext: ['groups' => ['treatment:update']],
        ),
        new Delete(
            security: "is_granted('ROLE_VETERINARIAN')",
        ),
    ],
    normalizationContext: ['groups' => ['treatment:read'], 'enable_max_depth' => true],
    denormalizationContext: ['groups' => ['treatment:write'], 'enable_max_depth' => true],
)]
class Treatment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['treatment:read', 'appointment:read', 'appointment:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['treatment:read', 'treatment:write', 'treatment:update', 'appointment:read', 'appointment:detail'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 1000)]
    #[Groups(['treatment:read', 'treatment:write', 'treatment:update', 'treatment:detail', 'appointment:detail'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['treatment:read', 'treatment:write', 'treatment:update', 'appointment:detail'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?float $price = null;

    #[ORM\Column]
    #[Groups(['treatment:read', 'treatment:write', 'treatment:update', 'treatment:detail'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $duration = null;

    #[ORM\ManyToMany(targetEntity: Appointment::class, mappedBy: 'treatments')]
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

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
            $appointment->addTreatment($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            $appointment->removeTreatment($this);
        }

        return $this;
    }
}