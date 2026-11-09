<?php

return [
    'proxies' => array_filter(array_map(
        static fn (string $ip): string => trim($ip),
        explode(',', env('TRUSTED_PROXIES', ''))
    )),
];
