<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use JoelButcher\Socialstream\Data\ProviderData;
use JoelButcher\Socialstream\Enums\ProviderEnum;
use JoelButcher\Socialstream\Providers;

beforeEach(function () {
    Providers::$buttonLabels = [];
});

it('builds from a known provider enum', function () {
    $data = ProviderData::from(ProviderEnum::Github);

    expect($data->toArray())
        ->toEqual([
            'id' => 'github',
            'name' => 'GitHub',
            'buttonLabel' => 'GitHub',
        ]);
});

it('builds from a known provider enum with a custom button label', function () {
    Providers::addLabelFor(ProviderEnum::Github, 'Continue with GH');
    $data = ProviderData::from(ProviderEnum::Github);

    expect($data->toArray())
        ->toEqual([
            'id' => 'github',
            'name' => 'GitHub',
            'buttonLabel' => 'Continue with GH',
        ]);
});

it('builds from a unknown provider string', function () {
    $data = ProviderData::from('my-provider');

    expect($data->toArray())
        ->toEqual([
            'id' => 'my-provider',
            'name' => 'My Provider',
            'buttonLabel' => 'My Provider',
        ]);
});

it('builds from a unknown provider string with a custom button label', function () {
    Providers::addLabelFor('my-provider', label: 'An Awesome Provider');
    $data = ProviderData::from('my-provider');

    expect($data->toArray())
        ->toEqual([
            'id' => 'my-provider',
            'name' => 'My Provider',
            'buttonLabel' => 'An Awesome Provider',
        ]);
});

it('builds from an array', function () {
    $data = ProviderData::from([
        'id' => 'my-provider',
        'name' => 'My Provider',
    ]);

    expect($data->toArray())
        ->toEqual([
            'id' => 'my-provider',
            'name' => 'My Provider',
            'buttonLabel' => 'My Provider',
        ]);
});

it('builds from an array with a custom button label', function () {
    $data = ProviderData::from([
        'id' => 'my-provider',
        'name' => 'My Provider',
        'label' => 'A Custom Button Label',
    ]);

    expect($data->toArray())
        ->toEqual([
            'id' => 'my-provider',
            'name' => 'My Provider',
            'buttonLabel' => 'A Custom Button Label',
        ]);
});
