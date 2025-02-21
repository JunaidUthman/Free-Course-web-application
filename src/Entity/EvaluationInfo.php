<?php

namespace App\Entity;

use App\Repository\EvaluationInfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvaluationInfoRepository::class)]
class EvaluationInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $result = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarque = null;

    #[ORM\ManyToOne(inversedBy: 'evaluationInfos')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $users = null;

    #[ORM\ManyToOne(inversedBy: 'evaluationInfos')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]

    private ?evaluation $evaluation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResult(): ?float
    {
        return $this->result;
    }

    public function setResult(?float $result): static
    {
        $this->result = $result;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getRemarque(): ?string
    {
        return $this->remarque;
    }

    public function setRemarque(?string $remarque): static
    {
        $this->remarque = $remarque;

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

    public function getEvaluation(): ?evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?evaluation $evaluation): static
    {
        $this->evaluation = $evaluation;

        return $this;
    }
}
