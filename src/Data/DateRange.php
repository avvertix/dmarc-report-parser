<?php

namespace Avvertix\DmarcReportParser\Data;

use DateRangeError;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Define a date range.
 *
 * @see DateRangeType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class DateRange
{
    public function __construct(
        public readonly DateTimeImmutable $begin,

        public readonly DateTimeImmutable $end,

    ) {

        if ($this->end < $this->begin) {
            throw new DateRangeError('Expecting end date to be after or equal begin date.');
        }

    }

    /**
     * Create a date range from values given in an associative array
     *
     * @param  array{begin: string, end: string}  $range  Both begin and end value must be a timestamp
     */
    public static function fromArray(array $range): self
    {

        if (empty($range['begin'] ?? null)) {
            throw new InvalidArgumentException('Missing begin timestamp.');
        }

        if (empty($range['end'] ?? null)) {
            throw new InvalidArgumentException('Missing end timestamp.');
        }

        $startDate = DateTimeImmutable::createFromFormat('U', $range['begin']);
        $endDate = DateTimeImmutable::createFromFormat('U', $range['end']);

        return new self(
            begin: $startDate,
            end: $endDate,
        );
    }
}
