<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->dateTime('assigned_at');
            $table->dateTime('returned_at')->nullable();

            $table->index(['item_id', 'returned_at']);
            $table->index(['user_id', 'returned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_assignments');
    }
};
