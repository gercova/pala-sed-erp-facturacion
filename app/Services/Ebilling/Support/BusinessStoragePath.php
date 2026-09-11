<?php

namespace App\Services\Ebilling\Support;

use App\Models\Business;

class BusinessStoragePath
{
    public function baseDirectory(Business $business): string
    {
        $segment = trim((string) ($business->ruc ?: 'business'));

        return public_path('files/ebilling/' . $segment);
    }

    public function xmlDirectory(Business $business): string
    {
        return $this->baseDirectory($business) . DIRECTORY_SEPARATOR . 'xml';
    }

    public function zipDirectory(Business $business): string
    {
        return $this->baseDirectory($business) . DIRECTORY_SEPARATOR . 'zip';
    }

    public function cdrDirectory(Business $business): string
    {
        return $this->baseDirectory($business) . DIRECTORY_SEPARATOR . 'cdr';
    }

    public function soapDirectory(Business $business): string
    {
        return $this->baseDirectory($business) . DIRECTORY_SEPARATOR . 'soap';
    }
}
