<?php

use App\Models\User;
use App\Models\Pet;
use App\Models\Slot;
use App\Filament\Owner\Resources\AppointmentResource;
use App\Filament\Owner\Resources\AppointmentResource\Pages\ListAppointments;
use App\Models\Appointment;
use Livewire\Livewire;

use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed();
    $this->ownerUser = User::whereName('Owner')->first();
    $this->doctorUser = User::whereName('Doctor')->first();
    actingAs($this->ownerUser);
});

// Owner Panel's AppointmentResource
it('can render the index page', function () {
    get(AppointmentResource::getUrl('index', panel:'owner'))
    ->assertOk();
});

it('can list appointments', function () {
    $appointments = Appointment::factory(3)
        ->for(Pet::factory())
        ->for(Slot::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();

    Livewire::test(ListAppointments::class)
        ->assertCanSeeTableRecords($appointments);
});
