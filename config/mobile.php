<?php

return [
    /*
    | Evenimentele și crash-urile brute se păstrează acest număr de zile,
    | apoi sunt numărate în rollup-uri zilnice și șterse.
    */
    'retention_days' => max(1, (int) env('MOBILE_RETENTION_DAYS', 21)),
];
