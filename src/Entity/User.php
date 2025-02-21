<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $fullname = null;

    /**
     * @var Collection<int, Course>
     */
    #[ORM\OneToMany(targetEntity: Course::class, mappedBy: 'users' , cascade: ['remove'], orphanRemoval: true)]
    private Collection $courses; 

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'users' , cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'users' , cascade: ['remove'], orphanRemoval: true)]
    private Collection $notifications;

    /**
     * @var Collection<int, EvaluationInfo>
     */
    #[ORM\OneToMany(targetEntity: EvaluationInfo::class, mappedBy: 'users' , cascade: ['remove'], orphanRemoval: true)]
    private Collection $evaluationInfos;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imagepath = null;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'user')]
    private Collection $likes;

    /**
     * @var Collection<int, Rates>
     */
    #[ORM\OneToMany(targetEntity: Rates::class, mappedBy: 'user')]
    private Collection $rates;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'sender')]
    private Collection $sent_notifications;

    /**
     * @var Collection<int, CheckedAccounts>
     */
    #[ORM\OneToMany(targetEntity: CheckedAccounts::class, mappedBy: 'admin')]
    private Collection $checkedAccounts;

    #[ORM\Column(nullable: true)]
    private ?bool $frozen = false;


    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->evaluationInfos = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->rates = new ArrayCollection();
        $this->sent_notifications = new ArrayCollection();
        $this->checkedAccounts = new ArrayCollection();
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
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
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
        // $this->plainPassword = null;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            $course->setUsers($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): static
    {
        if ($this->courses->removeElement($course)) {
            // set the owning side to null (unless already changed)
            if ($course->getUsers() === $this) {
                $course->setUsers(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setUsers($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUsers() === $this) {
                $comment->setUsers(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUsers($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUsers() === $this) {
                $notification->setUsers(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EvaluationInfo>
     */
    public function getEvaluationInfos(): Collection
    {
        return $this->evaluationInfos;
    }

    public function addEvaluationInfo(EvaluationInfo $evaluationInfo): static
    {
        if (!$this->evaluationInfos->contains($evaluationInfo)) {
            $this->evaluationInfos->add($evaluationInfo);
            $evaluationInfo->setUsers($this);
        }

        return $this;
    }

    public function removeEvaluationInfo(EvaluationInfo $evaluationInfo): static
    {
        if ($this->evaluationInfos->removeElement($evaluationInfo)) {
            // set the owning side to null (unless already changed)
            if ($evaluationInfo->getUsers() === $this) {
                $evaluationInfo->setUsers(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getImagepath(): ?string
    {
        return $this->imagepath;
    }

    public function setImagepath(?string $imagepath): static
    {
        $this->imagepath = $imagepath;

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setUser($this);
        }
        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Rates>
     */
    public function getRates(): Collection
    {
        return $this->rates;
    }

    public function addRate(Rates $rate): static
    {
        if (!$this->rates->contains($rate)) {
            $this->rates->add($rate);
            $rate->setUser($this);
        }

        return $this;
    }

    public function removeRate(Rates $rate): static
    {
        if ($this->rates->removeElement($rate)) {
            // set the owning side to null (unless already changed)
            if ($rate->getUser() === $this) {
                $rate->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getSentNotifications(): Collection
    {
        return $this->sent_notifications;
    }

    public function addSentNotification(Notification $sentNotification): static
    {
        if (!$this->sent_notifications->contains($sentNotification)) {
            $this->sent_notifications->add($sentNotification);
            $sentNotification->setSender($this);
        }

        return $this;
    }

    public function removeSentNotification(Notification $sentNotification): static
    {
        if ($this->sent_notifications->removeElement($sentNotification)) {
            // set the owning side to null (unless already changed)
            if ($sentNotification->getSender() === $this) {
                $sentNotification->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CheckedAccounts>
     */
    public function getCheckedAccounts(): Collection
    {
        return $this->checkedAccounts;
    }

    public function addCheckedAccount(CheckedAccounts $checkedAccount): static
    {
        if (!$this->checkedAccounts->contains($checkedAccount)) {
            $this->checkedAccounts->add($checkedAccount);
            $checkedAccount->setAdmin($this);
        }

        return $this;
    }

    public function removeCheckedAccount(CheckedAccounts $checkedAccount): static
    {
        if ($this->checkedAccounts->removeElement($checkedAccount)) {
            // set the owning side to null (unless already changed)
            if ($checkedAccount->getAdmin() === $this) {
                $checkedAccount->setAdmin(null);
            }
        }

        return $this;
    }

    public function isFrozen(): ?bool
    {
        return $this->frozen;
    }

    public function setFrozen(?bool $frozen): static
    {
        $this->frozen = $frozen;

        return $this;
    }
}
