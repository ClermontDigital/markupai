<?php

declare(strict_types=1);

namespace MarkupAI\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use MarkupAI\MarkupAiClient;

class MarkupAi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MarkupAiClient::class;
    }
}
