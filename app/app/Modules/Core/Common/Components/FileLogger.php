<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Components;

use Illuminate\Support\Carbon;
use JsonException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

abstract class FileLogger implements LoggerInterface
{
    use LoggerTrait;

    private string $filePath;

    /**
     * @param bool $silent Do not output to console (log only to file)
     */
    public function __construct(private readonly bool $silent = false)
    {
        $this->filePath = storage_path('logs/' . $this->getFileNameWithoutExtension() . '.log');
    }

    abstract public function getFileNameWithoutExtension(): string;

    /**
     * @param mixed $level
     *
     * @inheritDoc
     *
     * @throws JsonException
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $message = (string) $message;
        if (!empty($context)) {
            $message .= PHP_EOL . 'Context: ' . json_encode($context, JSON_THROW_ON_ERROR);
        }

        $message = '[' . Carbon::now()->format('Y-m-d H:i:s') . '] - (' . $level . ') - ' . $message . PHP_EOL;

        if (!$this->silent) {
            fwrite(STDOUT, $message);
        }

        file_put_contents($this->filePath, $message, FILE_APPEND);
    }
}
