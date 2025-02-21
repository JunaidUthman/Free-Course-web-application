<?php

namespace App\Entity;

use App\Repository\DeletedAccountsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeletedAccountsRepository::class)]
class DeletedAccounts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $date_delete = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDelete(): ?string
    {
        return $this->date_delete;
    }

    public function setDateDelete(?string $date_delete): static
    {
        $this->date_delete = $date_delete;

        return $this;
    }
}
