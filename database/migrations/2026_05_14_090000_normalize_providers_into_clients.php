<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $providers = DB::table('providers')->orderBy('id')->get();

        foreach ($providers as $provider) {
            $clientId = DB::table('clients')
                ->where('iddoc', $provider->iddoc)
                ->where('nro_documento', $provider->nro_documento)
                ->value('id');

            if (! $clientId) {
                $clientId = DB::table('clients')->insertGetId([
                    'iddoc' => $provider->iddoc,
                    'nro_documento' => $provider->nro_documento,
                    'nombres' => $provider->nombres,
                    'direccion' => $provider->direccion,
                    'codigo_pais' => $provider->codigo_pais,
                    'ubigeo' => $provider->ubigeo,
                    'telefono' => $provider->telefono,
                    'email' => $provider->email,
                    'created_at' => $provider->created_at ?? now(),
                    'updated_at' => $provider->updated_at ?? now(),
                ]);
            }

            DB::table('buys')
                ->where('idproveedor', $provider->id)
                ->update(['idproveedor' => $clientId]);
        }
    }

    public function down(): void
    {
    }
};
