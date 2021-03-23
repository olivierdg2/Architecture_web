<?php

namespace App\Entity;

class Step
{

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
