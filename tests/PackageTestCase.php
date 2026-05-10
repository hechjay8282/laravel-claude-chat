<?php

namespace Hakim\ClaudeChat\Tests;

use Hakim\ClaudeChat\ChatServiceProvider;
use Orchestra\Testbench\TestCase;

class PackageTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ChatServiceProvider::class];
    }
}
