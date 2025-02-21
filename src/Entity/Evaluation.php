<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(inversedBy: 'evaluation', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Course $courses = null;

    /**
     * @var Collection<int, EvaluationInfo>
     */
    #[ORM\OneToMany(targetEntity: EvaluationInfo::class, mappedBy: 'evaluation' , cascade: ['remove'], orphanRemoval: true)]
    private Collection $evaluationInfos;

    /**
     * @var Collection<int, Questions>
     */
    #[ORM\OneToMany(targetEntity: Questions::class, mappedBy: 'evaluation' , cascade: ['remove'], orphanRemoval: true)]
    private Collection $questions;

    public function __construct()
    {
        $this->evaluationInfos = new ArrayCollection();
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
            $evaluationInfo->setEvaluation($this);
        }

        return $this;
    }

    public function removeEvaluationInfo(EvaluationInfo $evaluationInfo): static
    {
        if ($this->evaluationInfos->removeElement($evaluationInfo)) {
            // set the owning side to null (unless already changed)
            if ($evaluationInfo->getEvaluation() === $this) {
                $evaluationInfo->setEvaluation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Questions>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Questions $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setEvaluation($this);
        }

        return $this;
    }

    public function removeQuestion(Questions $question): static
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getEvaluation() === $this) {
                $question->setEvaluation(null);
            }
        }

        return $this;
    }
}
