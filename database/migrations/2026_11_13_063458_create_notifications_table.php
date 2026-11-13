<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Pemilik postingan/komentar yang diberi tahu
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); // Orang yang melakukan aksi
            $table->foreignId('post_id')->nullable()->constrained()->onDelete('cascade'); // Postingan terkait
            $table->text('message'); // Isi pesan (cth: "Ahmad mengomentari status Anda")
            $table->boolean('is_read')->default(false); // Status sudah dibaca atau belum
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
