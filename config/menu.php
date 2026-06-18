<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu PDF Letterhead
    |--------------------------------------------------------------------------
    |
    | Path to the letterhead PDF used as the background for menu PDFs.
    | Set to null to disable. The PDF is rasterised once (via GhostScript)
    | at the resolution below and the PNG is cached in storage/app/.
    |
    */

    'pdf_letterhead' => env('MENU_LETTERHEAD_PATH', null) ?: public_path('Menu_Letter_Head.pdf'),

    'pdf_letterhead_dpi' => 150,

];
