<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;

    protected $table = 'billings';

    protected $fillable = [
        'idtipo_comprobante',
        'serie',
        'correlativo',
        'fecha_emision',
        'fecha_vencimiento',
        'hora',
        'idcliente',
        'idmoneda',
        'idpago',
        'modo_pago',
        'sunat_forma_pago',
        'exonerada',
        'inafecta',
        'gravada',
        'anticipo',
        'igv',
        'icbper',
        'gratuita',
        'otros_cargos',
        'total',
        'monto_credito',
        'cuotas',
        'payment_breakdown',
        'observaciones',
        'cdr',
        'anulado',
        'id_tipo_nota_credito',
        'id_tipo_nota_debito',
        'idfactura_anular',
        'motivo',
        'estado_cpe',
        'sunat_status',
        'sunat_intentos',
        'sunat_ultimo_intento_at',
        'estado_whatsapp',
        'whatsapp_error',
        'whatsapp_intentos',
        'whatsapp_enviado_at',
        'errores',
        'nticket',
        'idusuario',
        'idarqueocaja',
        'vuelto',
        'qr',
        'idalmacen',
    ];

    public const SUNAT_STATUS_PENDIENTE = 'pendiente';

    public const SUNAT_STATUS_ENVIADO = 'enviado';

    public const SUNAT_STATUS_ACEPTADO = 'aceptado';

    public const SUNAT_STATUS_RECHAZADO = 'rechazado';

    public const SUNAT_STATUS_ERROR_COMUNICACION = 'error_comunicacion';

    public const WPP_STATUS_PENDIENTE = 'pendiente';

    public const WPP_STATUS_ENVIADO = 'enviado';

    public const WPP_STATUS_FALLIDO = 'fallido';

    public const WPP_STATUS_SIN_TELEFONO = 'sin_telefono';

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'sunat_ultimo_intento_at' => 'datetime',
        'whatsapp_enviado_at' => 'datetime',
        'sunat_intentos' => 'integer',
        'whatsapp_intentos' => 'integer',
        'exonerada' => 'decimal:2',
        'inafecta' => 'decimal:2',
        'gravada' => 'decimal:2',
        'anticipo' => 'decimal:2',
        'igv' => 'decimal:2',
        'icbper' => 'decimal:2',
        'gratuita' => 'decimal:2',
        'otros_cargos' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_credito' => 'decimal:2',
        'cuotas' => 'array',
        'payment_breakdown' => 'array',
        'anulado' => 'boolean',
        'vuelto' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Client::class, 'idcliente');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'idmoneda');
    }

    public function typeDocument()
    {
        return $this->belongsTo(TypeDocument::class, 'idtipo_comprobante');
    }

    public function payMode()
    {
        return $this->belongsTo(PayMode::class, 'idpago');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }

    public function archingCash()
    {
        return $this->belongsTo(ArchingCash::class, 'idarqueocaja');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'idalmacen');
    }

    public function noteType()
    {
        return $this->belongsTo(CreditNoteType::class, 'id_tipo_nota_credito');
    }

    public function creditNoteType()
    {
        return $this->belongsTo(CreditNoteType::class, 'id_tipo_nota_credito');
    }

    public function debitNoteType()
    {
        return $this->belongsTo(DebitNoteType::class, 'id_tipo_nota_debito');
    }

    public function parentBilling()
    {
        return $this->belongsTo(self::class, 'idfactura_anular');
    }

    public function details()
    {
        return $this->hasMany(DetailBilling::class, 'idfacturacion');
    }
}
