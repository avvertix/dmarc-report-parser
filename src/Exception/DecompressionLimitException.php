<?php

namespace Avvertix\DmarcReportParser\Exception;

use RuntimeException;
use Throwable;

/**
 * Thrown when a compressed report expands beyond the configured cap.
 *
 * Distinct from UnsupportedFormatException so a caller can tell "too large"
 * apart from "corrupt" and answer 413 rather than 422.
 */
final class DecompressionLimitException extends RuntimeException
{
    /**
     * Instantiate a DecompressionLimitException
     */
    public function __construct(string $fileName, int $maxDecompressedBytes, ?Throwable $previous = null)
    {
        parent::__construct("File [{$fileName}] expands beyond the maximum allowed size of [{$maxDecompressedBytes}] bytes.", 413, $previous);
    }
}
