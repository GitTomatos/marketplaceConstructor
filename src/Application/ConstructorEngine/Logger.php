<?php

declare(strict_types=1);

namespace App\Application\ConstructorEngine;

final class Logger
{
    private string $fullLogFileName;

    public function __construct(string $fullLogFilePath, string $logFileName, bool $rewriteAtFirst = true)
    {
        if (!is_dir($fullLogFilePath)) {
            if (!mkdir($fullLogFilePath) && !is_dir($fullLogFilePath)) {
                throw new \RuntimeException(sprintf('Директория "%s" не была создана', $fullLogFilePath));
            }
        }

        if ($rewriteAtFirst) {
            file_put_contents("$fullLogFilePath/$logFileName", '');
        }

        $this->fullLogFileName = "$fullLogFilePath/$logFileName";
    }


    public function log(string $message): void
    {
        $message .= PHP_EOL;
        file_put_contents($this->fullLogFileName, $message, FILE_APPEND);
    }
}