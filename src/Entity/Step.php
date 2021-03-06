<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;

class Step
{
    /**
     * @Groups("recette:read")
    */
    private $Step;

    public function getStep(): ?string
    {
        return $this->Step;
    }

    public function setStep(string $Step): self
    {
        $this->Step = $Step;

        return $this;
    }
    
}
