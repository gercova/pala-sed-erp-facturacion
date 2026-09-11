<?php

namespace App\Services\Ebilling\Support;

use App\Models\Business;

class BusinessCertificatePath
{
    public function relativePathFor(Business $business): ?string
    {
        $relativePath = trim((string) ($business->certificado ?? ''));

        return $relativePath !== '' ? $relativePath : null;
    }

    public function absolutePathFor(Business $business): ?string
    {
        $relativePath = $this->relativePathFor($business);

        if ($relativePath === null) {
            return null;
        }

        return public_path($relativePath);
    }
}
