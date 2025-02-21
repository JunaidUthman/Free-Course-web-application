<?php

namespace App\Entity;

use App\Repository\QuestionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionsRepository::class)]
class Questions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $choice1 = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $choice2 = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $choice3 = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $correct_answer = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]

    private ?evaluation $evaluation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getChoice1(): ?string
    {
        return $this->choice1;
    }

    public function setChoice1(string $choice1): static
    {
        $this->choice1 = $choice1;

        return $this;
    }

    public function getChoice2(): ?string
    {
        return $this->choice2;
    }

    public function setChoice2(string $choice2): static
    {
        $this->choice2 = $choice2;

        return $this;
    }

    public function getChoice3(): ?string
    {
        return $this->choice3;
    }

    public function setChoice3(string $choice3): static
    {
        $this->choice3 = $choice3;

        return $this;
    }

    public function getCorrectAnswer(): ?string
    {
        return $this->correct_answer;
    }

    public function setCorrectAnswer(string $correct_answer): static
    {
        $this->correct_answer = $correct_answer;

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
