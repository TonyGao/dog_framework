<?php

namespace App\Message;

class ExportEmployeeMessage
{
    private string $userId;
    private array $filters;

    public function __construct(string $userId, array $filters)
    {
        $this->userId = $userId;
        $this->filters = $filters;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
