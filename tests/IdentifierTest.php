<?php

use Avvertix\DmarcReportParser\Data\Identifier;

it('create from array', function () {

    $identifiers = Identifier::fromArray([
        'envelope_to' => 'other-domain.localhost',
        'envelope_from' => 'a-domain.localhost',
        'header_from' => 'a-domain.localhost',
    ]);

    expect($identifiers)
        ->toBeInstanceOf(Identifier::class);

    expect($identifiers->envelope_to)
        ->toEqual('other-domain.localhost');

    expect($identifiers->envelope_from)
        ->toEqual('a-domain.localhost');

    expect($identifiers->header_from)
        ->toEqual('a-domain.localhost');
});

it('handle null envelope_to', function () {

    $identifiers = Identifier::fromArray([
        'envelope_from' => 'a-domain.localhost',
        'header_from' => 'a-domain.localhost',
    ]);

    expect($identifiers)
        ->toBeInstanceOf(Identifier::class);

    expect($identifiers->envelope_to)
        ->toBeEmpty();

    expect($identifiers->envelope_from)
        ->toEqual('a-domain.localhost');

    expect($identifiers->header_from)
        ->toEqual('a-domain.localhost');
});
