<?php

namespace App\Entity;

use Monolog\Logger;
use App\Entity\Step;
use App\Entity\Ingredient;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\RecetteRepository;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=RecetteRepository::class)
 */
class Recette
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("recette:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("recette:read")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="recettes")
     * @Groups("recette:read")
     */
    private $category;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("recette:read")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="array")
     * @Groups("recette:read")
     */
    private $Ingredients = [];

    /**
     * @ORM\Column(type="array")
     * @Groups("recette:read")
     */
    private $Preparation = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("recette:read")
     */
    private $image;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIngredients(): ?array
    {
        return $this->Ingredients;
    }

    public function setIngredients(array $Ingredients): self
    {
        $this->Ingredients = $Ingredients;

        return $this;
    }

    public function addIngredient(Ingredient $ing): void
    {
        array_push($this->Ingredients,$ing);
    }

    public function removeIngredient(Ingredient $ing): void
    {
        $key = array_search($ing,$this->getIngredients());
        unset($this->getIngredients()[$key]);
    }

    public function getPreparation(): ?array
    {
        return $this->Preparation;
    }

    public function setPreparation(array $Preparation): self
    {
        $this->Preparation = $Preparation;

        return $this;
    }

    public function addPreparation(Step $step): void
    {
        array_push($this->Preparation,$step);
    }

    public function removePreparation(Step $step): void
    {
        $key = array_search($step,$this->getPreparation());
        unset($this->getPreparation()[$key]);
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image): self
    {
        $this->image = $image;

        return $this;
    }
}
