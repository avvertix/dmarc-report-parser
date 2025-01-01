<?php

use Avvertix\DmarcReportParser\Data\AuthResult;
use Avvertix\DmarcReportParser\Data\Identifier;
use Avvertix\DmarcReportParser\Data\Record;
use Avvertix\DmarcReportParser\Data\Row;

it('create from array', function () {

    $record = Record::fromArray([
        'row' => [
            'source_ip' => '255.255.255.253',
            'count' => '1',
            'policy_evaluated' => [
                'disposition' => 'none',
                'dkim' => 'pass',
                'spf' => 'fail',
            ],
        ],
        'identifiers' => [
            'envelope_to' => 'other-domain.localhost',
            'envelope_from' => 'a-domain.localhost',
            'header_from' => 'a-domain.localhost',
        ],
        'auth_results' => [
            'dkim' => [
                'domain' => 'a-domain.localhost',
                'selector' => 'dkim',
                'result' => 'pass',
            ],
            'spf' => [
                'domain' => 'a-domain.localhost',
                'scope' => 'mfrom',
                'result' => 'temperror',
            ],
        ],
    ]);

    expect($record)
        ->toBeInstanceOf(Record::class);

    expect($record->row)
        ->toBeInstanceOf(Row::class);

    expect($record->identifiers)
        ->toBeInstanceOf(Identifier::class);

    expect($record->auth_results)
        ->toBeInstanceOf(AuthResult::class);
});
