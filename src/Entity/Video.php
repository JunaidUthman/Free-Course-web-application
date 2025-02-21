<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT , nullable : true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT , nullable : true)]
    private ?string $rating = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Course $courses = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'videos' , cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $VideoImage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $videoPath = null;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'Video')]
    private Collection $_likes;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->_likes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(string $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getCourses(): ?Course
    {
        return $this->courses;
    }

    public function setCourses(?Course $courses): static
    {
        $this->courses = $courses;

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
            $comment->setVideos($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getVideos() === $this) {
                $comment->setVideos(null);
            }
        }

        return $this;
    }

    public function getVideoImage(): ?string
    {
        return $this->VideoImage;
    }

    public function setVideoImage(?string $VideoImage): static
    {
        $this->VideoImage = $VideoImage;

        return $this;
    }

    public function getVideoPath(): ?string
    {
        return $this->videoPath;
    }

    public function setVideoPath(?string $videoPath): static
    {
        $this->videoPath = $videoPath;

        return $this;
    }

    public function addLike(Like $like): static
    {
        if (!$this->_likes->contains($like)) {
            $this->_likes->add($like);
            $like->setVideo($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->_likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getVideo() === $this) {
                $like->setVideo(null);
            }
        }

        return $this;
    }

    public function countLikes(){
        return $this->_likes->count();
    }

    public function getLikes(){
        return $this->_likes;
    }
}
