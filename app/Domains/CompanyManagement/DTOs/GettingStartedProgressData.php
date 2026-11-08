<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\DTOs;

final readonly class GettingStartedProgressData
{
    /**
     * @param  array<int, GettingStartedStepData>  $steps
     */
    public function __construct(
        public array $steps,
        public bool $isDismissed,
    ) {}

    public function completed(): int
    {
        return count(array_filter($this->steps, fn (GettingStartedStepData $step) => $step->isCompleted));
    }

    public function total(): int
    {
        return count($this->steps);
    }

    public function percentage(): int
    {
        if ($this->total() === 0) {
            return 100;
        }

        return (int) round(($this->completed() / $this->total()) * 100);
    }

    public function isComplete(): bool
    {
        return $this->completed() === $this->total();
    }

    public function nextStep(): ?GettingStartedStepData
    {
        foreach ($this->steps as $step) {
            if (! $step->isCompleted) {
                return $step;
            }
        }

        return null;
    }
}
