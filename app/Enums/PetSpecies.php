<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PetSpecies: string implements HasLabel
{
    case Bearded_Dragon = 'Bearded Dragon';
    case Birds = 'Birds';
    case Burro = 'Burro';
    case Cats = 'Cats';
    case Chameleons_Veiled = 'Chameleons (Veiled)';
    case Chickens = 'Chickens';
    case Chinchillas = 'Chinchillas';
    case Chinese_Water_Dragon = 'Chinese Water Dragon';
    case Cows = 'Cows';
    case Dogs = 'Dogs';
    case Donkey = 'Donkey';
    case Ducks = 'Ducks';
    case Ferrets = 'Ferrets';
    case Fish = 'Fish';
    case Geckos = 'Geckos';
    case Geese = 'Geese (Chinese Swan Goose)';
    case Gerbils = 'Gerbils';
    case Goats = 'Goats';
    case Guinea_Fowl = 'Guinea Fowl';
    case Guinea_Pigs = 'Guinea Pigs';
    case Hamsters = 'Hamsters';
    case Hedgehogs = 'Hedgehogs';
    case Horses = 'Horses';
    case Iguanas = 'Iguanas';
    case Llamas = 'Llamas';
    case Lizards = 'Lizards';
    case Mice = 'Mice';
    case Mule = 'Mule';
    case Peafowl = 'Peafowl';
    case Pigs_and_Hogs = 'Pigs and Hogs';
    case Pigeons = 'Pigeons';
    case Ponies = 'Ponies';
    case Pot_Bellied_Pig = 'Pot Bellied Pig';
    case Rabbits = 'Rabbits';
    case Rats = 'Rats';
    case Sheep = 'Sheep';
    case Skinks = 'Skinks';
    case Snakes = 'Snakes';
    case Stick_Insects = 'Stick Insects';
    case Sugar_Gliders = 'Sugar Gliders';
    case Tarantula = 'Tarantula';
    case Turkeys = 'Turkeys';
    case Turtles = 'Turtles';
    case Others = 'Others';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Bearded_Dragon => 'Bearded Dragon',
            self::Birds => 'Birds',
            self::Burro => 'Burro',
            self::Cats => 'Cats',
            self::Chameleons_Veiled => 'Chameleons (Veiled)',
            self::Chickens => 'Chickens',
            self::Chinchillas => 'Chinchillas',
            self::Chinese_Water_Dragon => 'Chinese Water Dragon',
            self::Cows => 'Cows',
            self::Dogs => 'Dogs',
            self::Donkey => 'Donkey',
            self::Ducks => 'Ducks',
            self::Ferrets => 'Ferrets',
            self::Fish => 'Fish',
            self::Geckos => 'Geckos',
            self::Geese => 'Geese (Chinese Swan Goose)',
            self::Gerbils => 'Gerbils',
            self::Goats => 'Goats',
            self::Guinea_Fowl => 'Guinea Fowl',
            self::Guinea_Pigs => 'Guinea Pigs',
            self::Hamsters => 'Hamsters',
            self::Hedgehogs => 'Hedgehogs',
            self::Horses => 'Horses',
            self::Iguanas => 'Iguanas',
            self::Llamas => 'Llamas',
            self::Lizards => 'Lizards',
            self::Mice => 'Mice',
            self::Mule => 'Mule',
            self::Peafowl => 'Peafowl',
            self::Pigs_and_Hogs => 'Pigs and Hogs',
            self::Pigeons => 'Pigeons',
            self::Ponies => 'Ponies',
            self::Pot_Bellied_Pig => 'Pot Bellied Pig',
            self::Rabbits => 'Rabbits',
            self::Rats => 'Rats',
            self::Sheep => 'Sheep',
            self::Skinks => 'Skinks',
            self::Snakes => 'Snakes',
            self::Stick_Insects => 'Stick Insects',
            self::Sugar_Gliders => 'Sugar Gliders',
            self::Tarantula => 'Tarantula',
            self::Turkeys => 'Turkeys',
            self::Turtles => 'Turtles',
            self::Others => 'Others',
        };
    }
}
