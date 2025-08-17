<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_message_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_message_id')->constrained()->cascadeOnDelete();
            $t->string('disk')->default('public');
            $t->string('path');
            $t->string('original_name');
            $t->unsignedBigInteger('size')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_message_attachments');
    }
};
