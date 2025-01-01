<?php

use Avvertix\DmarcReportParser\Data\AlignmentMode;
use Avvertix\DmarcReportParser\Data\DispositionType;
use Avvertix\DmarcReportParser\Data\Policy;

it('create from array', function () {

    $policy = Policy::fromArray([
        'domain' => 'a-domain.localhost',
        'adkim' => 's',
        'aspf' => 's',
        'p' => 'reject',
        'sp' => 'reject',
        'pct' => '100',
        'fo' => '0',
    ]);

    expect($policy)
        ->toBeInstanceOf(Policy::class);

    expect($policy->domain)
        ->toEqual('a-domain.localhost');

    expect($policy->p)
        ->toEqual(DispositionType::REJECT);

    expect($policy->sp)
        ->toEqual(DispositionType::REJECT);

    expect($policy->pct)
        ->toEqual(100);

    expect($policy->fo)
        ->toEqual('0');

    expect($policy->adkim)
        ->toEqual(AlignmentMode::STRICT);

    expect($policy->aspf)
        ->toEqual(AlignmentMode::STRICT);
});

it('handle null alignment modes', function () {

    $policy = Policy::fromArray([
        'domain' => 'a-domain.localhost',
        'p' => 'reject',
        'sp' => 'reject',
        'pct' => '100',
        'fo' => '0',
    ]);

    expect($policy)
        ->toBeInstanceOf(Policy::class);

    expect($policy->domain)
        ->toEqual('a-domain.localhost');

    expect($policy->p)
        ->toEqual(DispositionType::REJECT);

    expect($policy->sp)
        ->toEqual(DispositionType::REJECT);

    expect($policy->pct)
        ->toEqual(100);

    expect($policy->fo)
        ->toEqual('0');

    expect($policy->adkim)
        ->toBeEmpty();

    expect($policy->aspf)
        ->toBeEmpty();
});
