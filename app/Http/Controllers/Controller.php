<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Billing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\Business;
use App\Models\Country;
use App\Models\DetailQuote;
use App\Models\DetailSaleNote;
use App\Models\District;
use App\Models\Province;
use App\Models\Quote;
use App\Models\SaleNote;
use App\Models\StockProduct;
use App\Models\TransferOrder;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function verify__client($dni_ruc)
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI1ODgiLCJuYW1lIjoiTXl0ZW1zIiwiZW1haWwiOiJteXRlbXNjb250YWN0b0BnbWFpbC5jb20iLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJjb25zdWx0b3IifQ.CMerOf33h1rSeWSEtfPwOv_6_vLhC0ZyhseiQs5Ba6c';
        $endpoint = strlen((string) $dni_ruc) === 8
            ? 'https://api.factiliza.com/pe/v1/dni/info/' . $dni_ruc
            : 'https://api.factiliza.com/pe/v1/ruc/info/' . $dni_ruc;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false || $error !== '') {
            return (object) [
                'status' => 500,
                'message' => 'No se pudo consultar el documento en este momento.',
                'error' => $error,
            ];
        }

        return json_decode($response);
    }

    public function get_ubigeo($ubigeo)
    {
        $data = [];
        $distrito = District::where('codigo', $ubigeo)->first();

        if (! $distrito) {
            return $data;
        }

        $data['distrito'] = $distrito->descripcion;
        $provincia = Province::where('codigo', $distrito->provincia_codigo)
            ->where('departamento_codigo', $distrito->departamento_codigo)
            ->first();
        $data['provincia'] = $provincia?->descripcion;
        $departamento = Department::where('codigo', $distrito->departamento_codigo)->first();
        $data['departamento'] = $departamento?->descripcion;

        return $data;
    }

    public function signo_pais() {
        $country = $this->resolveBusinessCountry();

        return $country?->signo ?: 'S/';
    }

    public function moneda_pais() {
        $country = $this->resolveBusinessCountry();

        return $country?->moneda ?: 'SOLES';
    }

    public function redondeado($numero, $decimales = 2) {
        $factor = pow(10, $decimales);
        return (round($numero*$factor)/$factor); 
    }

    protected function currentWarehouseId(): int
    {
        if (! auth()->check()) {
            return 0;
        }

        return (int) (session('selected_warehouse_id') ?: auth()->user()->idalmacen ?: 0);
    }

    protected function saleNotesByCurrentWarehouse()
    {
        $warehouseId = $this->currentWarehouseId();
        $query = SaleNote::query();

        if ($warehouseId <= 0) {
            return $query;
        }

        return $query->whereExists(function ($subquery) use ($warehouseId) {
            $subquery->selectRaw('1')
                ->from('detail_sale_notes')
                ->whereColumn('detail_sale_notes.idnotaventa', 'sale_notes.id')
                ->where('detail_sale_notes.idalmacen', $warehouseId);
        });
    }

    protected function quotesByCurrentWarehouse()
    {
        $warehouseId = $this->currentWarehouseId();
        $query = Quote::query();

        if ($warehouseId <= 0) {
            return $query;
        }

        return $query->whereExists(function ($subquery) use ($warehouseId) {
            $subquery->selectRaw('1')
                ->from('detail_quotes')
                ->whereColumn('detail_quotes.idcotizacion', 'quotes.id')
                ->where('detail_quotes.idalmacen', $warehouseId);
        });
    }

    protected function billingsByCurrentWarehouse()
    {
        $warehouseId = $this->currentWarehouseId();
        $query = Billing::query();

        if ($warehouseId <= 0) {
            return $query;
        }

        return $query->where('billings.idalmacen', $warehouseId);
    }

    protected function stockProductsByCurrentWarehouse()
    {
        $warehouseId = $this->currentWarehouseId();
        $query = StockProduct::query();

        if ($warehouseId <= 0) {
            return $query;
        }

        return $query->where('idalmacen', $warehouseId);
    }

    protected function transferOrdersByCurrentWarehouse()
    {
        $warehouseId = $this->currentWarehouseId();
        $query = TransferOrder::query();

        if ($warehouseId <= 0) {
            return $query;
        }

        return $query->where(function ($inner) use ($warehouseId) {
            $inner->where('idalmacen_despacho', $warehouseId)
                ->orWhere('idalmacen_receptor', $warehouseId)
                ->orWhere('idalmacen', $warehouseId);
        });
    }

    private function resolveBusinessCountry(): ?Country
    {
        $business = Business::query()->find(1);

        if ($business && $business->idpais) {
            $country = Country::query()->find($business->idpais);

            if ($country) {
                return $country;
            }
        }

        return Country::query()->where('prefijo', 'PE')->first();
    }
}
