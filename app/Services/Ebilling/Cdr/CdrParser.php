<?php

namespace App\Services\Ebilling\Cdr;

use DOMDocument;
use InvalidArgumentException;
use ZipArchive;

class CdrParser
{
    public function extract(string $zipPath, string $extractDirectory): array
    {
        if (! is_file($zipPath)) {
            throw new InvalidArgumentException('No existe el ZIP del CDR.');
        }

        if (! is_dir($extractDirectory) && ! mkdir($extractDirectory, 0755, true) && ! is_dir($extractDirectory)) {
            throw new InvalidArgumentException('No se pudo crear la carpeta de extraccion del CDR.');
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new InvalidArgumentException('No se pudo abrir el ZIP del CDR.');
        }

        $zip->extractTo($extractDirectory);
        $zip->close();

        $xmlFiles = glob($extractDirectory . DIRECTORY_SEPARATOR . '*.XML');

        if ($xmlFiles === false || $xmlFiles === []) {
            $xmlFiles = glob($extractDirectory . DIRECTORY_SEPARATOR . '*.xml');
        }

        if ($xmlFiles === false || $xmlFiles === []) {
            throw new InvalidArgumentException('No se encontro el XML dentro del CDR.');
        }

        $xmlPath = $xmlFiles[0];
        $document = new DOMDocument();
        $document->loadXML((string) file_get_contents($xmlPath));

        return [
            'xml_path' => $xmlPath,
            'response_code' => $this->nodeValue($document, 'ResponseCode'),
            'description' => $this->nodeValue($document, 'Description'),
            'notes' => $this->nodeValues($document, 'Note'),
        ];
    }

    private function nodeValue(DOMDocument $document, string $tagName): ?string
    {
        $node = $document->getElementsByTagName($tagName)->item(0);

        return $node ? $node->nodeValue : null;
    }

    private function nodeValues(DOMDocument $document, string $tagName): array
    {
        $values = [];

        foreach ($document->getElementsByTagName($tagName) as $node) {
            $values[] = $node->nodeValue;
        }

        return $values;
    }
}
