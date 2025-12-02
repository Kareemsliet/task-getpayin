<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PREPARING = "preparing";
    case SUCCESS = "success";
    case FAILURE = "failure";

    public function isSuccess():bool
    {
        return $this === self::SUCCESS;
    }

    public function isFailure(): bool
    {
        return $this === self::FAILURE;
    }

    public function isPrepare(): bool
    {
        return $this === self::PREPARING;
    }
}
