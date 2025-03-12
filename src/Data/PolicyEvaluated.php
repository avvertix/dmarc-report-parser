<?php

namespace Avvertix\DmarcReportParser\Data;

/**
 * @see PolicyEvaluatedType https://datatracker.ietf.org/doc/html/rfc7489#autoid-87
 */
final class PolicyEvaluated
{
    public function __construct(

        public readonly DispositionType $disposition,

        public readonly DmarcResultType $dkim,

        public readonly DmarcResultType $spf,

        /**
         * Reasons that may affect DMARC disposition or execution thereof
         *
         * @var PolicyOverrideReason[]
         */
        public readonly array $reasons = [],

    ) {}

    public static function fromArray(array $policy): self
    {
        $reasons = $policy['reason'] ?? [];

        if($reasons['type'] ?? false){
            $reasons = [$reasons];
        }

        return new self(
            disposition: DispositionType::from($policy['disposition']),
            dkim: DmarcResultType::from($policy['dkim']),
            spf: DmarcResultType::from($policy['spf']),
            reasons: empty($reasons) ? [] : array_map(fn ($item) => PolicyOverrideReason::fromArray($item), $reasons),
        );
    }
}
