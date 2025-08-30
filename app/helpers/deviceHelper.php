<?php

class DeviceHelper
{
    public static function mapModel($code)
    {
        $mapping = [
            "SM-A528B" => "Samsung Galaxy A52s",
            "SM-M326B" => "Samsung Galaxy M32 5G",
            "SM-G996B" => "Samsung Galaxy S21+",
            "M2101K6G" => "Xiaomi Redmi Note 10 Pro",
            "CPH2219"  => "Oppo Reno 6",
            // Tambah sendiri sesuai kebutuhan
        ];

        return $mapping[$code] ?? $code; // fallback: tampilkan kode asli kalau tidak ketemu
    }
}
