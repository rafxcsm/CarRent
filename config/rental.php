<?php

return [
    // Capstone policy default. Change this value if the project policy changes.
    'senior_pwd_discount_percent' => (float) env('RENTAL_SENIOR_PWD_DISCOUNT_PERCENT', 20),
    'timezone' => env('RENTAL_TIMEZONE', 'Asia/Manila'),
];
