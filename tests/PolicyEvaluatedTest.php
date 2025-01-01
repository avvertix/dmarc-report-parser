<?php

use Avvertix\DmarcReportParser\Data\DispositionType;
use Avvertix\DmarcReportParser\Data\DmarcResultType;
use Avvertix\DmarcReportParser\Data\PolicyEvaluated;
use Avvertix\DmarcReportParser\Data\PolicyOverrideReason;
use Avvertix\DmarcReportParser\Data\PolicyOverrideType;

it('create from array', function () {

    $policy = PolicyEvaluated::fromArray([
        'disposition' => 'none',
        'dkim' => 'pass',
        'spf' => 'fail',
    ]);

    expect($policy)
        ->toBeInstanceOf(PolicyEvaluated::class);

    expect($policy->dkim)
        ->toEqual(DmarcResultType::PASS);

    expect($policy->spf)
        ->toEqual(DmarcResultType::FAIL);

    expect($policy->disposition)
        ->toEqual(DispositionType::NONE);

});

it('handle reason', function () {

    $policy = PolicyEvaluated::fromArray([
        'disposition' => 'none',
        'dkim' => 'pass',
        'spf' => 'fail',
        'reason' => [
            [
                'type' => 'mailing_list',
            ],
        ],
    ]);

    expect($policy)
        ->toBeInstanceOf(PolicyEvaluated::class);

    expect($policy->dkim)
        ->toEqual(DmarcResultType::PASS);

    expect($policy->spf)
        ->toEqual(DmarcResultType::FAIL);

    expect($policy->disposition)
        ->toEqual(DispositionType::NONE);

    expect($policy->reasons)
        ->toHaveCount(1)
        ->toContainOnlyInstancesOf(PolicyOverrideReason::class);

    expect($policy->reasons[0])
        ->type->toEqual(PolicyOverrideType::MAILING_LIST)
        ->comment->toBeNull();

});

it('handle reason with comment', function () {

    $policy = PolicyEvaluated::fromArray([
        'disposition' => 'none',
        'dkim' => 'pass',
        'spf' => 'fail',
        'reason' => [
            [
                'type' => 'other',
                'comment' => 'a comment',
            ],
        ],
    ]);

    expect($policy)
        ->toBeInstanceOf(PolicyEvaluated::class);

    expect($policy->dkim)
        ->toEqual(DmarcResultType::PASS);

    expect($policy->spf)
        ->toEqual(DmarcResultType::FAIL);

    expect($policy->disposition)
        ->toEqual(DispositionType::NONE);

    expect($policy->reasons)
        ->toHaveCount(1)
        ->toContainOnlyInstancesOf(PolicyOverrideReason::class);

    expect($policy->reasons[0])
        ->type->toEqual(PolicyOverrideType::OTHER)
        ->comment->toEqual('a comment');

});
