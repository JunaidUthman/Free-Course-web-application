<?php

namespace App\Entity;

use App\Repository\CheckedAccountsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CheckedAccountsRepository::class)]
class CheckedAccounts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'checkedAccounts')]
    private ?User $admin = null;

    #[ORM\ManyToOne(inversedBy: 'checkedAccounts')]
    private ?User $checkedUser = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): static
    {
        $this->admin = $admin;

        return $this;
    }

    public function getCheckedUser(): ?User
    {
        return $this->checkedUser;
    }

    public function setCheckedUser(?User $checkedUser): static
    {
        $this->checkedUser = $checkedUser;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): static
    {
        $this->date = $date;

        return $this;
    }
}
