<?php

use App\Models\User;
use App\Models\Pet;
use App\Models\Slot;
use App\Models\Clinic;
use App\Filament\Owner\Resources\AppointmentResource;
use App\Filament\Owner\Resources\AppointmentResource\Pages\CreateAppointment;
use App\Filament\Owner\Resources\AppointmentResource\Pages\ListAppointments;
use App\Models\Appointment;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed();
    $this->ownerUser = User::whereName('Owner')->first();
    $this->doctorUser = User::whereName('Doctor')->first();
    actingAs($this->ownerUser);
    //Fake storage disk
    Storage::fake('avatars');
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
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();

    Livewire::test(ListAppointments::class)
        ->assertCanSeeTableRecords($appointments)
        ->assertSeeText($appointments[0]->pet->name)
        ->assertSeeText($appointments[0]->description)
        ->assertSeeText($appointments[0]->doctor->name)
        ->assertSeeText($appointments[0]->clinic->name)
        ->assertSeeText($appointments[0]->date)
        ->assertSeeText($appointments[0]->slot->formattedTime)
        ->assertSeeText($appointments[0]->status->name);
});

it('only shows appointments for owned pet', function() {
    $myPet = Pet::factory()
        ->for($this->ownerUser, 'owner');

    $anotherPet = Pet::factory()->create();

    $myPetAppointment = Appointment::factory()
        ->for($myPet)
        ->for(Slot::factory())
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();

    $anotherPetAppointment = Appointment::factory()
        ->for($anotherPet)
        ->for(Slot::factory())
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();

    get(AppointmentResource::getUrl('index', panel:'owner'))
        ->assertOk()
        ->assertSeeText($myPetAppointment->pet->name)
        ->assertDontSeeText($anotherPetAppointment->pet->name);
});

// it('show pet avatars', function () {
//     $appointment = Appointment::factory()
//         ->for(Pet::factory())
//         ->for(Slot::factory())
//         ->for(Clinic::factory())
//         ->state(['doctor_id' => $this->doctorUser->id])
//         ->create();

//     Livewire::test(ListAppointments::class)
//         ->assertSee($appointment->pet->avatar);
// });

it('can create appointment', function () {
    $appointment = Appointment::factory()
        ->for(Pet::factory())
        ->for(Slot::factory())
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->make();

    Livewire::test(CreateAppointment::class)
        ->fillForm([
            'pet_id' => $appointment->pet_id,
            'clinic_id' => $appointment->clinic_id,
            'doctor_id' =>  $appointment->owner_id,
            'slot_id' =>  $appointment->slot_id,
            'date' =>  $appointment->date,
            'description' => $appointment->description,
            'status' => $appointment->status,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Appointment::class, [
        'pet_id' => $appointment->pet_id,
        'clinic_id' => $appointment->clinic_id,
        'doctor_id' =>  $appointment->owner_id,
        'slot_id' =>  $appointment->slot_id,
        'date' =>  $appointment->date,
        'description' => $appointment->description,
        'status' => $appointment->status,
    ]);
});
