<?php

namespace Avvertix\DmarcReportParser\Exception;

use RuntimeException;
use Throwable;

final class UnsupportedFormatException extends RuntimeException
{
    /**
     * Instantiate a UnsupportedFormatException
     */
    public function __construct(string $mimeType, string $fileName, ?Throwable $previous = null)
    {
        parent::__construct("Unsupported format [{$mimeType}] for file [{$fileName}]. Expecting xml, zip, gzip.", 500, $previous);
    }
}
