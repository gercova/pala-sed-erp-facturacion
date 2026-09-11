<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'warehouse_id']);
        });

        $now = now();
        $rows = DB::table('users')
            ->select('id', 'idalmacen')
            ->whereNotNull('idalmacen')
            ->get()
            ->filter(fn ($user) => (int) $user->idalmacen > 0)
            ->map(fn ($user) => [
                'user_id' => (int) $user->id,
                'warehouse_id' => (int) $user->idalmacen,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if (! empty($rows)) {
            DB::table('user_warehouse')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_warehouse');
    }
};
