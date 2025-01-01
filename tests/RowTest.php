<?php

use Avvertix\DmarcReportParser\Data\PolicyEvaluated;
use Avvertix\DmarcReportParser\Data\Row;

it('create from array', function () {

    $row = Row::fromArray([
        'source_ip' => '255.255.255.253',
        'count' => '1',
        'policy_evaluated' => [
            'disposition' => 'none',
            'dkim' => 'pass',
            'spf' => 'fail',
        ],
    ]);

    expect($row)
        ->toBeInstanceOf(Row::class);

    expect($row->source_ip)
        ->toEqual('255.255.255.253');

    expect($row->count)
        ->toEqual(1);

    expect($row->policy_evaluated)
        ->toBeInstanceOf(PolicyEvaluated::class);
});
