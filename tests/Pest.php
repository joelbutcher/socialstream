<?php

use JoelButcher\Socialstream\Tests\OrchestraTestCase;

pest()->printer()->compact();

uses(OrchestraTestCase::class)->in('Feature', 'Unit');
