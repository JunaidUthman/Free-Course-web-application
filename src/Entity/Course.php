<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $language = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $category = null;

    #[ORM\Column(type: Types::INTEGER , nullable: true)]
    private ?int $rating = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT , nullable: true)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $users = null;

    /**
     * @var Collection<int, Video>
     */
    #[ORM\OneToMany(targetEntity: Video::class, mappedBy: 'courses' , cascade: ['remove'], orphanRemoval: true)]
    private Collection $videos;

    #[ORM\OneToOne(mappedBy: 'courses', cascade: ['remove'])]
    private ?Evaluation $evaluation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $courseImage = null;

    /**
     * @var Collection<int, Rates>
     */
    #[ORM\OneToMany(targetEntity: Rates::class, mappedBy: 'course')]
    private Collection $rates;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $level = null;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
        $this->rates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): static
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setCourses($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getCourses() === $this) {
                $video->setCourses(null);
            }
        }

        return $this;
    }

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $evaluation): static
    {
        // unset the owning side of the relation if necessary
        if ($evaluation === null && $this->evaluation !== null) {
            $this->evaluation->setCourses(null);
        }

        // set the owning side of the relation if necessary
        if ($evaluation !== null && $evaluation->getCourses() !== $this) {
            $evaluation->setCourses($this);
        }

        $this->evaluation = $evaluation;

        return $this;
    }

    public function getCourseImage(): ?string
    {
        return $this->courseImage;
    }

    public function setCourseImage(?string $courseImage): static
    {
        $this->courseImage = $courseImage;

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
            $rate->setCourse($this);
        }

        return $this;
    }

    public function removeRate(Rates $rate): static
    {
        if ($this->rates->removeElement($rate)) {
            // set the owning side to null (unless already changed)
            if ($rate->getCourse() === $this) {
                $rate->setCourse(null);
            }
        }

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): static
    {
        $this->level = $level;

        return $this;
    }
}
