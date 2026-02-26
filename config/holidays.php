<?php

return [
    /*
    | Libur nasional tetap (setiap tahun). Format: 'm-d' => 'Nama libur'
    */
    'fixed' => [
        '01-01' => 'Tahun Baru Masehi',
        '05-01' => 'Hari Buruh Internasional',
        '06-01' => 'Hari Lahir Pancasila',
        '08-17' => 'HUT Kemerdekaan RI',
        '12-25' => 'Hari Raya Natal',
    ],

    /*
    | Libur nasional yang berubah tiap tahun (SKB 3 Menteri).
    | Format: 'Y' => [ 'Y-m-d' => 'Nama libur', ... ]
    */
    'by_year' => [
        '2025' => [
            '2025-01-27' => 'Isra Mikraj Nabi Muhammad SAW',
            '2025-01-28' => 'Cuti bersama Tahun Baru Imlek',
            '2025-01-29' => 'Tahun Baru Imlek 2576',
            '2025-03-28' => 'Cuti bersama Nyepi',
            '2025-03-29' => 'Hari Suci Nyepi',
            '2025-03-31' => 'Idul Fitri 1446 H',
            '2025-04-01' => 'Idul Fitri 1446 H',
            '2025-04-02' => 'Cuti bersama Idul Fitri',
            '2025-04-03' => 'Cuti bersama Idul Fitri',
            '2025-04-04' => 'Cuti bersama Idul Fitri',
            '2025-04-07' => 'Cuti bersama Idul Fitri',
            '2025-04-18' => 'Wafat Yesus Kristus',
            '2025-04-20' => 'Paskah',
            '2025-05-12' => 'Hari Raya Waisak 2569',
            '2025-05-13' => 'Cuti bersama Waisak',
            '2025-05-29' => 'Kenaikan Yesus Kristus',
            '2025-05-30' => 'Cuti bersama Kenaikan Isa',
            '2025-06-06' => 'Idul Adha 1446 H',
            '2025-06-09' => 'Cuti bersama Idul Adha',
            '2025-06-27' => 'Tahun Baru Islam 1447 H',
            '2025-09-05' => 'Maulid Nabi Muhammad SAW',
            '2025-12-26' => 'Cuti bersama Natal',
        ],
        '2026' => [
            '2026-01-01' => 'Tahun Baru Masehi',
            '2026-01-27' => 'Tahun Baru Imlek',
            '2026-03-19' => 'Hari Suci Nyepi',
            '2026-03-21' => 'Idul Fitri 1447 H',
            '2026-03-22' => 'Idul Fitri 1447 H',
            '2026-04-03' => 'Wafat Yesus Kristus',
            '2026-04-05' => 'Paskah',
            '2026-05-01' => 'Hari Buruh Internasional',
            '2026-05-02' => 'Hari Raya Waisak',
            '2026-05-18' => 'Kenaikan Yesus Kristus',
            '2026-06-01' => 'Hari Lahir Pancasila',
            '2026-05-27' => 'Idul Adha 1447 H',
            '2026-06-18' => 'Tahun Baru Islam 1448 H',
            '2026-08-17' => 'HUT Kemerdekaan RI',
            '2026-08-25' => 'Maulid Nabi Muhammad SAW',
            '2026-12-25' => 'Hari Raya Natal',
        ],
    ],
];
