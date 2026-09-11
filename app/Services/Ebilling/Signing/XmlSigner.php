<?php

namespace App\Services\Ebilling\Signing;

use InvalidArgumentException;

class XmlSigner
{
    public function sign(string $xmlPath, string $certificatePath, string $certificatePassword): array
    {
        if (! is_file($xmlPath)) {
            throw new InvalidArgumentException('No existe el XML a firmar.');
        }

        if (! is_file($certificatePath)) {
            throw new InvalidArgumentException('No existe el certificado configurado para firmar.');
        }

        require_once public_path('api_sunat/signature.php');

        $signature = new \Signature();
        $result = $signature->signature_xml('0', $xmlPath, $certificatePath, $certificatePassword);

        if (($result['respuesta'] ?? null) !== 'ok') {
            throw new InvalidArgumentException('No se pudo firmar el XML.');
        }

        return $result;
    }
}
