<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * @see RowType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class Row
{
    public function __construct(

        /**
         * The connecting IP
         */
        public readonly string $source_ip,

        /**
         * The number of matching messages.
         */
        public readonly int $count,

        /**
         * The DMARC disposition applying to matching messages.
         */
        public readonly PolicyEvaluated $policy_evaluated,

    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            source_ip: $row['source_ip'],
            count: intval($row['count'], 10),
            policy_evaluated: PolicyEvaluated::fromArray($row['policy_evaluated']),
        );
    }
}
