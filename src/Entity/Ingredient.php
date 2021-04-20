<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
class Ingredient
{
    /**
     * @Groups("recette:read")
     */
    private $Ingredient;

    /**
     * @Groups("recette:read")
     */
    private $Quantity;

    public function getIngredient(): ?string
    {
        return $this->Ingredient;
    }

    public function setIngredient(string $Ingredient): self
    {
        $this->Ingredient = $Ingredient;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->Quantity;
    }

    public function setQuantity(string $Quantity): self
    {
        $this->Quantity = $Quantity;

        return $this;
    }
    
}
