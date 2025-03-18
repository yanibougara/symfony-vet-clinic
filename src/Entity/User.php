<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\Patch;
use App\State\UserPasswordHasherProcessor;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_DIRECTOR')",
            normalizationContext: ['groups' => ['user:read']],
        ),
        new Get(
            security: "is_granted('ROLE_DIRECTOR') or object == user",
            normalizationContext: ['groups' => ['user:read', 'user:detail']],
        ),
        new Post(
            // security: "is_granted('ROLE_DIRECTOR')",
            processor: UserPasswordHasherProcessor::class,
            // denormalizationContext: ['groups' <=> ['user:write']],
        ),
        new Put(
            security: "is_granted('ROLE_DIRECTOR') or object == user",
            denormalizationContext: ['groups' => ['user:update']],
            processor: UserPasswordHasherProcessor::class,
        ),
        new Patch(processor: UserPasswordHasherProcessor::class),
        new Delete(
            security: "is_granted('ROLE_DIRECTOR')",
        ),
    ],
    normalizationContext: ['groups' => ['user:read'], 'enable_max_depth' => true],
    denormalizationContext: ['groups' => ['user:write'], 'enable_max_depth' => true],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write', 'user:update'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write', 'user:update'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    // #[Groups(['user:write', 'user:update'])]
    #[Groups(['user:read'])]
    private ?string $password = null;

    #[Assert\NotBlank]
    #[Groups('user:write')]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'user:update', 'appointment:read'])]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'user:update', 'appointment:read'])]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[ORM\OneToMany(mappedBy: 'veterinarian', targetEntity: Appointment::class)]
    #[Groups(['user:detail'])]
    #[MaxDepth(1)]
    private Collection $veterinarianAppointments;

    #[ORM\OneToMany(mappedBy: 'assistant', targetEntity: Appointment::class)]
    #[Groups(['user:detail'])]
    #[MaxDepth(1)]
    private Collection $assistantAppointments;

    public function __construct()
    {
        $this->veterinarianAppointments = new ArrayCollection();
        $this->assistantAppointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
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

    /**
     * @return Collection<int, Appointment>
     */
    public function getVeterinarianAppointments(): Collection
    {
        return $this->veterinarianAppointments;
    }

    public function addVeterinarianAppointment(Appointment $appointment): static
    {
        if (!$this->veterinarianAppointments->contains($appointment)) {
            $this->veterinarianAppointments->add($appointment);
            $appointment->setVeterinarian($this);
        }

        return $this;
    }

    public function removeVeterinarianAppointment(Appointment $appointment): static
    {
        if ($this->veterinarianAppointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getVeterinarian() === $this) {
                $appointment->setVeterinarian(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAssistantAppointments(): Collection
    {
        return $this->assistantAppointments;
    }

    public function addAssistantAppointment(Appointment $appointment): static
    {
        if (!$this->assistantAppointments->contains($appointment)) {
            $this->assistantAppointments->add($appointment);
            $appointment->setAssistant($this);
        }

        return $this;
    }

    public function removeAssistantAppointment(Appointment $appointment): static
    {
        if ($this->assistantAppointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getAssistant() === $this) {
                $appointment->setAssistant(null);
            }
        }

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }
 
    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
 
        return $this;
    }
}