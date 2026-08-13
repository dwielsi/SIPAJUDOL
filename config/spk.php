<?php

return [

    /**
     * Kriteria SAW. Semua bertipe "benefit" (nilai makin tinggi = makin prioritas).
     * Total weight harus berjumlah 1.0.
     *
     * "max" adalah nilai maksimum tetap (skala absolut) untuk normalisasi, supaya semua
     * alternatif dinormalisasi dengan penggaris yang sama dan tidak berubah-ubah tiap ada
     * data scan baru. Nilai raw yang melebihi "max" akan dibatasi (capped) ke normalisasi 1.0.
     */
    'criteria' => [
        'malware_count' => [
            'label' => 'Jumlah Malware',
            'weight' => 0.25,
            'type' => 'benefit',
            'max' => 20,
        ],
        'judol_link_count' => [
            'label' => 'Jumlah Link Judi Online',
            'weight' => 0.25,
            'type' => 'benefit',
            'max' => 50,
        ],
        'infected_pages' => [
            'label' => 'Halaman Terinfeksi',
            'weight' => 0.20,
            'type' => 'benefit',
            'max' => 30,
        ],
        'keyword_count' => [
            'label' => 'Jumlah Keyword Judol',
            'weight' => 0.10,
            'type' => 'benefit',
            'max' => 50,
        ],
        'redirect_count' => [
            'label' => 'Jumlah Redirect Mencurigakan',
            'weight' => 0.10,
            'type' => 'benefit',
            'max' => 20,
        ],
        'external_link_count' => [
            'label' => 'Jumlah Link Eksternal Mencurigakan',
            'weight' => 0.10,
            'type' => 'benefit',
            'max' => 30,
        ],
    ],

    /**
     * Ambang batas skor (0..1) untuk label level prioritas di tabel hasil.
     */
    'priority_thresholds' => [
        'tinggi' => 0.6,
        'sedang' => 0.3,
    ],

];
