<?php

namespace Yasapurnama\DocumentWatermark;

class WatermarkFactory
{
    protected static $mime = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'WordWatermark',
        'application/pdf'                                                         => 'PDFWatermark'
    ];

    public static function load(string $documentPath)
    {
        $fileMime      = mime_content_type($documentPath);
        $providerClass = 'Yasapurnama\\DocumentWatermark\\' . static::$mime[$fileMime];
   
        if (class_exists($providerClass)) {
            return (new $providerClass($documentPath));
        }

        throw new \Exception('Document Watermark provider not found');
    }
}