<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proximidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->boolean('sistema')->default(true)->comment('True para proximidades pré-definidas pelo sistema');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // Índices
            $table->unique('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proximidades');
    }
};
