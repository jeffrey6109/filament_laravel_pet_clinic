<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public $ownerUser;
    public $adminUser;
    public $doctorUser;

    protected function setUp(): void
    {
        parent::setUp();
    }
}
