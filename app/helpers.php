<?php

if (! function_exists('formatWeight')) {
    /**
     * Format berat yang disimpan dalam satuan Gram di database
     * menjadi satuan Kg agar seragam.
     *
     * @param int|float $grams
     * @return string
     */
    function formatWeight($grams)
    {
        $kg = $grams / 1000;
        $formatted = (floor($kg) == $kg) ? number_format($kg, 0, ',', '.') : number_format($kg, 2, ',', '.');
        return $formatted . ' Kg';
    }
}

if (! function_exists('formatQuantity')) {
    /**
     * Format angka kuantitas agar tidak ada desimal .00 jika bulat,
     * tapi tetap menampilkan desimal jika ada.
     *
     * @param int|float $value
     * @return string
     */
    function formatQuantity($value)
    {
        return (floor($value) == $value) ? number_format($value, 0, ',', '.') : number_format($value, 2, ',', '.');
    }
}
