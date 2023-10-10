<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date_of_birth');
            $table->string('species'); // e.g Dog, Cat, Lizard, Turtle
            $table->string('type'); // e.g Bulldog, Persian cat, Gecko, Sulcata Tortoise
            $table->string('avatar')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('owners');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
