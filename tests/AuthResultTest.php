<?php

use Avvertix\DmarcReportParser\Data\AuthResult;
use Avvertix\DmarcReportParser\Data\DkimAuthResult;
use Avvertix\DmarcReportParser\Data\SpfAuthResult;

it('create from array', function () {

    $result = AuthResult::fromArray([
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
    ]);

    expect($result)
        ->toBeInstanceOf(AuthResult::class);

    expect($result->dkim)
        ->toHaveCount(1)
        ->toContainOnlyInstancesOf(DkimAuthResult::class);

    expect($result->spf)
        ->toHaveCount(1)
        ->toContainOnlyInstancesOf(SpfAuthResult::class);

});

it('handle multiple dkim and spf', function () {

    $result = AuthResult::fromArray([
        'dkim' => [
            [
                'domain' => 'a-domain.localhost',
                'selector' => 'dkim',
                'result' => 'pass',
            ],
            [
                'domain' => 'other-domain.localhost',
                'selector' => 'dkim',
                'result' => 'pass',
            ],
        ],
        'spf' => [
            [
                'domain' => 'a-domain.localhost',
                'scope' => 'mfrom',
                'result' => 'temperror',
            ],
            [
                'domain' => 'other-domain.localhost',
                'scope' => 'mfrom',
                'result' => 'temperror',
            ],
        ],
    ]);

    expect($result)
        ->toBeInstanceOf(AuthResult::class);

    expect($result->dkim)
        ->toHaveCount(2)
        ->toContainOnlyInstancesOf(DkimAuthResult::class);

    expect($result->spf)
        ->toHaveCount(2)
        ->toContainOnlyInstancesOf(SpfAuthResult::class);

});

it('create from array with only spf result', function () {

    $result = AuthResult::fromArray([
        'spf' => [
            'domain' => 'a-domain.localhost',
            'scope' => 'mfrom',
            'result' => 'temperror',
        ],
    ]);

    expect($result)
        ->toBeInstanceOf(AuthResult::class);

    expect($result->dkim)
        ->toHaveCount(0);

    expect($result->spf)
        ->toHaveCount(1)
        ->toContainOnlyInstancesOf(SpfAuthResult::class);

});

it('handle dkim human result', function () {

    $result = AuthResult::fromArray([
        'dkim' => [
            'domain' => 'a-domain.localhost',
            'selector' => 'dkim',
            'result' => 'pass',
            'human_result' => 'additional data',
        ],
        'spf' => [
            'domain' => 'a-domain.localhost',
            'scope' => 'mfrom',
            'result' => 'temperror',
        ],
    ]);

    expect($result)
        ->toBeInstanceOf(AuthResult::class);

    expect($result->dkim)
        ->toHaveCount(1)
        ->toContainOnlyInstancesOf(DkimAuthResult::class);

    expect($result->spf)
        ->toHaveCount(1)
        ->toContainOnlyInstancesOf(SpfAuthResult::class);

});

it('handle missing dkim selector', function () {

    $result = AuthResult::fromArray([
        'dkim' => [
            'domain' => 'a-domain.localhost',
            'result' => 'pass',
        ],
        'spf' => [
            'domain' => 'a-domain.localhost',
            'scope' => 'mfrom',
            'result' => 'temperror',
        ],
    ]);

    expect($result)
        ->toBeInstanceOf(AuthResult::class);

    expect($result->dkim)
        ->toHaveCount(1)
        ->toContainOnlyInstancesOf(DkimAuthResult::class);

    expect($result->spf)
        ->toHaveCount(1)
        ->toContainOnlyInstancesOf(SpfAuthResult::class);

});
