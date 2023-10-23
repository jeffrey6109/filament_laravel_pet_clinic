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

    protected function setUp(): void {
        parent::setUp();
        // Setup the test environment.
         $this->seed();

        $this->adminUser = User::whereName('Admin')->first();
        $this->ownerUser = User::whereName('Owner')->first();
        $this->doctorUser = User::whereName('Doctor')->first();
    }
}
