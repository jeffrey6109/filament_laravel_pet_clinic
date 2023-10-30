<?php
use App\Filament\Owner\Resources\PetResource;
use App\Filament\Owner\Resources\PetResource\Pages\ListPets;
use App\Filament\Resources\PetResource\Pages\CreatePet;
use App\Models\Pet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Livewire\Livewire;

use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;


beforeEach(function () {
    seed();
    $this->ownerUser = User::whereName('Owner')->first();
    actingAs($this->ownerUser);
});

// Owner Panel's PetResource
it('renders the index page', function ()
{
    get(PetResource::getUrl('index', panel:'owner'))
        ->assertOk();
});

it('renders the create page', function ()
{
    get(PetResource::getUrl('create', panel:'owner'))
        ->assertOk();
});

it('renders the edit page', function ()
{
    $pet = Pet::factory()->create();

    get(PetResource::getUrl('edit', ['record' => $pet], panel:'owner'))
        ->assertOk();
});

it('can list pets', function ()
{
    $pets = Pet::factory(3)
        ->for($this->ownerUser, relationship: 'owner')
        ->create();

    Livewire::test(ListPets::class)
        ->assertCanSeeTableRecords($pets)
        ->assertSeeText([
            $pets[0]->name,
            $pets[0]->date_of_birth->format(config('app.date_format')),
            $pets[0]->type,
            $pets[0]->species,

            $pets[1]->name,
            $pets[1]->date_of_birth->format(config('app.date_format')),
            $pets[1]->type,
            $pets[1]->species,

            $pets[2]->name,
            $pets[2]->date_of_birth->format(config('app.date_format')),
            $pets[2]->type,
            $pets[2]->species,

        ]);
});

it('only show pets for the current owner', function () {
    $myPet = Pet::factory()
        ->for($this->ownerUser, relationship: 'owner')
        ->create();

    // $otherPet = Pet::factory()
    //     ->for(
    //         User::factory()->for(
    //             Role::whereName('owner')->first()
    //         )
    //         ->create(), relationship: 'owner'
    //     )->create();

    $otherOwner =  User::factory()->role('owner')->create();

    $otherPet = Pet::factory()
        ->for($otherOwner, relationship: 'owner')->create();

        Livewire::test(ListPets::class)
        ->assertSeeText($myPet->name)
        ->assertDontSeeText($otherPet->name);
});

it('can create pets', function () {
    $newPet = Pet::factory()
        ->for($this->ownerUser, relationship: 'owner')
        ->make();

    Livewire::test(CreatePet::class)
        ->fillForm([
            'name' => $newPet->name,
            'date_of_birth' => $newPet->date_of_birth,
            'type' =>  $newPet->type,
            'species' => $newPet->species,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Pet::class, [
            'name' => $newPet->name,
            'date_of_birth' => $newPet->date_of_birth,
            'type' =>  $newPet->type,
            'species' => $newPet->species,
        ]);
});
