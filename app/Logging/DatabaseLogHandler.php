<?php

namespace App\Logging;

use App\Models\ErrorLogEntry;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Throwable;

class DatabaseLogHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        $exception = $record->context['exception'] ?? null;

        ErrorLogEntry::create([
            'level' => $record->level->getName(),
            'message' => $record->message,
            'context' => $this->safeContext($record->context),
            'exception_class' => $exception instanceof Throwable ? $exception::class : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function safeContext(array $context): array
    {
        unset($context['exception']);

        return $context;
    }
}
