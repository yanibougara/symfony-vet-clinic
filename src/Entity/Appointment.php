<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\AppointmentRepository;
use App\State\AppointmentProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ASSISTANT') or is_granted('ROLE_VETERINARIAN') or is_granted('ROLE_DIRECTOR')",
            normalizationContext: ['groups' => ['appointment:read']],
        ),
        new Get(
            security: "is_granted('ROLE_ASSISTANT') or is_granted('ROLE_VETERINARIAN') or is_granted('ROLE_DIRECTOR')",
            normalizationContext: ['groups' => ['appointment:read', 'appointment:detail']],
        ),
        new Post(
            security: "is_granted('ROLE_ASSISTANT')",
            denormalizationContext: ['groups' => ['appointment:write']],
            processor: AppointmentProcessor::class,
        ),
        new Put(
            security: "is_granted('ROLE_ASSISTANT') or (is_granted('ROLE_VETERINARIAN') and object.getVeterinarian() == user)",
            denormalizationContext: ['groups' => ['appointment:update']],
        ),
    ],
    normalizationContext: ['groups' => ['appointment:read'], 'enable_max_depth' => true],
    denormalizationContext: ['groups' => ['appointment:write'], 'enable_max_depth' => true],
)]
class Appointment
{
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['appointment:read', 'user:detail', 'animal:detail'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['appointment:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['appointment:read', 'appointment:write', 'appointment:update', 'user:detail', 'animal:detail'])]
    #[Assert\NotBlank]
    #[Assert\GreaterThan('now')]
    private ?\DateTimeInterface $appointmentDate = null;

    #[ORM\Column(length: 255)]
    #[Groups(['appointment:read', 'appointment:write', 'appointment:update', 'user:detail', 'animal:detail'])]
    #[Assert\NotBlank]
    private ?string $reason = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['appointment:read', 'appointment:write'])]
    #[Assert\NotBlank]
    private ?Animal $animal = null;

    #[ORM\ManyToOne(inversedBy: 'assistantAppointments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['appointment:read', 'appointment:write'])]
    private ?User $assistant = null;

    #[ORM\ManyToOne(inversedBy: 'veterinarianAppointments')]
    #[Groups(['appointment:read', 'appointment:update'])]
    private ?User $veterinarian = null;

    #[ORM\Column(length: 20)]
    #[Groups(['appointment:read', 'appointment:update', 'user:detail', 'animal:detail'])]
    private ?string $status = self::STATUS_SCHEDULED;

    #[ORM\ManyToMany(targetEntity: Treatment::class, inversedBy: 'appointments')]
    #[Groups(['appointment:read', 'appointment:detail', 'appointment:update'])]
    #[MaxDepth(1)]
    private Collection $treatments;

    #[ORM\Column(nullable: true)]
    #[Groups(['appointment:read', 'appointment:update'])]
    private ?bool $isPaid = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->treatments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAppointmentDate(): ?\DateTimeInterface
    {
        return $this->appointmentDate;
    }

    public function setAppointmentDate(\DateTimeInterface $appointmentDate): static
    {
        $this->appointmentDate = $appointmentDate;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): static
    {
        $this->animal = $animal;

        return $this;
    }

    public function getAssistant(): ?User
    {
        return $this->assistant;
    }

    public function setAssistant(?User $assistant): static
    {
        $this->assistant = $assistant;

        return $this;
    }

    public function getVeterinarian(): ?User
    {
        return $this->veterinarian;
    }

    public function setVeterinarian(?User $veterinarian): static
    {
        $this->veterinarian = $veterinarian;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED])) {
            throw new \InvalidArgumentException("Invalid status");
        }
        
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Treatment>
     */
    public function getTreatments(): Collection
    {
        return $this->treatments;
    }

    public function addTreatment(Treatment $treatment): static
    {
        if (!$this->treatments->contains($treatment)) {
            $this->treatments->add($treatment);
        }

        return $this;
    }

    public function removeTreatment(Treatment $treatment): static
    {
        $this->treatments->removeElement($treatment);

        return $this;
    }

    public function isIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(?bool $isPaid): static
    {
        $this->isPaid = $isPaid;

        return $this;
    }
}