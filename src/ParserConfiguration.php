<?php

namespace Avvertix\DmarcReportParser;

final class ParserConfiguration
{
    /**
     * The default cap on decompressed report size.
     *
     * A large aggregate report is a few megabytes uncompressed, so 10 MB leaves
     * room for real reports while keeping a compression bomb from expanding
     * without bound.
     *
     * Default to 10 MB which covers a report with about 32k records.
     */
    public const int DEFAULT_MAX_DECOMPRESSED_BYTES = 10 * 1024 * 1024;

    /**
     * Instantiate a ParserConfiguration
     *
     * @param  int  $maxDecompressedBytes  Maximum size a compressed report is allowed to expand to
     */
    public function __construct(
        public int $maxDecompressedBytes = self::DEFAULT_MAX_DECOMPRESSED_BYTES,
    ) {}
}
