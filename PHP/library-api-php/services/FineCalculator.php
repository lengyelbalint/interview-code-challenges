<?php

final class FineCalculator
{
    public function __construct(private float $finePerDay = 1.00)
    {
    }

    public function calculateAmount(int $overdueDays): float
    {
        if ($overdueDays <= 0) {
            return 0.0;
        }
        return round($overdueDays * $this->finePerDay, 2);
    }
}