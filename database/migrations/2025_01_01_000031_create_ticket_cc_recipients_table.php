<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_cc_recipients', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $t->string('email')->index();
            $t->boolean('active')->default(true)->index();
            $t->timestamps();
            $t->unique(['ticket_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_cc_recipients');
    }
};
