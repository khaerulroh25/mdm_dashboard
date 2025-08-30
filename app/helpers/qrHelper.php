<?php
require_once __DIR__ . '/../../vendor/autoload.php'; 
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class QrHelper {
    public static function generate($data) {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->size(250)
            ->margin(10)
            ->build();

        // hasil dalam bentuk data URI (langsung bisa dipakai di <img>)
        return $result->getDataUri();
    }
}
