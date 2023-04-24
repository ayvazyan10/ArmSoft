<?php

/**
 * Configuration for the ArmSoft API package.
 *
 * Note: This package is currently in beta version and the ArmSoft API is not yet finished.
 */

return [
    // Required: Your ArmSoft client ID
    'clientId' => env('ARM_SOFT_CLIENT_ID', '00000000-0000-0000-0000-000000000000'),

    // Required: Your ArmSoft client secret
    'secret' => env('ARM_SOFT_SECRET', '000000000000'),

    // Required: The ID of your ArmSoft database
    'dbId' => env('ARM_SOFT_DB_ID', '00000'),

    // Optional: The price type to use for price-related API calls (01 - wholesale, 02 - retail, 03 - purchase price)
    'priceType' => '02',

    // Optional: The language to use in API responses
    'language' => 'en-US,en;q=0.5',

    // Optional: Additional settings to pass to the ArmSoft API
    'settings' => [
        'ShowProgress' => false,
        'ShowColumns' => false,
    ],
];
