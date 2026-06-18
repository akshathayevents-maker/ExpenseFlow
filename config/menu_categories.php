<?php

/**
 * Menu Composer — static category definitions.
 *
 * `items`        — food categories (stored in menu_items.category_key)
 * `sections`     — legacy fixed sections (kept for backward-compat migration only)
 * `meal_sections`— dynamic section types available in the composer
 */

return [

    // ── Food category definitions ──────────────────────────────────────────
    'items' => [
        'welcome_drink'  => ['en' => 'Welcome Drink',   'ta' => 'வரவேற்பு பானம்'],
        'sweet'          => ['en' => 'Sweet',            'ta' => 'ஸ்வீட்'],
        'starter'        => ['en' => 'Starter',          'ta' => 'ஸ்டார்டர்'],
        'soup'           => ['en' => 'Soup',             'ta' => 'சூப்'],
        'salad'          => ['en' => 'Salad',            'ta' => 'சாலட்'],
        'main_course'    => ['en' => 'Main Course',      'ta' => 'முக்கிய உணவு'],
        'rice'           => ['en' => 'Rice',             'ta' => 'சாதம்'],
        'indian_bread'   => ['en' => 'Indian Bread',     'ta' => 'ரொட்டி வகைகள்'],
        'gravy'          => ['en' => 'Gravy',            'ta' => 'குழம்பு'],
        'dry_curry'      => ['en' => 'Dry Curry',        'ta' => 'உலர் கறி'],
        'poriyal'        => ['en' => 'Poriyal',          'ta' => 'பொரியல்'],
        'kootu'          => ['en' => 'Kootu',            'ta' => 'கூட்டு'],
        'dessert'        => ['en' => 'Dessert',          'ta' => 'இனிப்பு'],
        'ice_cream'      => ['en' => 'Ice Cream',        'ta' => 'ஐஸ்கிரீம்'],
        'live_counter'   => ['en' => 'Live Counter',     'ta' => 'லைவ் கவுண்டர்'],
        'beverages'      => ['en' => 'Beverages',        'ta' => 'பானங்கள்'],
        'evening_snacks' => ['en' => 'Evening Snacks',  'ta' => 'மாலை சிற்றுண்டி'],
        'fruit'          => ['en' => 'Fruit',            'ta' => 'பழங்கள்'],
        'beeda'          => ['en' => 'Beeda',            'ta' => 'பீடா'],
        'other'          => ['en' => 'Other',            'ta' => 'மற்றவை'],
    ],

    // ── Legacy fixed sections (migration reference only) ───────────────────
    'sections' => [
        'breakfast'      => ['en' => 'Breakfast',       'ta' => 'காலை உணவு'],
        'lunch'          => ['en' => 'Lunch',            'ta' => 'மதிய உணவு'],
        'dinner'         => ['en' => 'Dinner',           'ta' => 'இரவு உணவு'],
        'evening_snacks' => ['en' => 'Evening Snacks',  'ta' => 'மாலை சிற்றுண்டி'],
    ],

    // ── Dynamic meal section types (composer picker) ───────────────────────
    'meal_sections' => [
        'breakfast'        => ['en' => 'Breakfast',        'ta' => 'காலை உணவு',          'icon' => 'bi-sun'],
        'lunch'            => ['en' => 'Lunch',             'ta' => 'மதிய உணவு',           'icon' => 'bi-brightness-high'],
        'dinner'           => ['en' => 'Dinner',            'ta' => 'இரவு உணவு',           'icon' => 'bi-moon-stars'],
        'evening_snacks'   => ['en' => 'Evening Snacks',   'ta' => 'மாலை சிற்றுண்டி',    'icon' => 'bi-cup-hot'],
        'high_tea'         => ['en' => 'High Tea',          'ta' => 'ஹை டீ',              'icon' => 'bi-cup-straw'],
        'welcome_drink'    => ['en' => 'Welcome Drink',     'ta' => 'வரவேற்பு பானம்',     'icon' => 'bi-cup'],
        'midnight_snacks'  => ['en' => 'Midnight Snacks',  'ta' => 'நள்ளிரவு சிற்றுண்டி','icon' => 'bi-stars'],
        'reception_dinner' => ['en' => 'Reception Dinner', 'ta' => 'வரவேற்பு இரவு உணவு', 'icon' => 'bi-balloon-heart'],
        'special_menu'     => ['en' => 'Special Menu',     'ta' => 'சிறப்பு மெனு',       'icon' => 'bi-star'],
        'custom'           => ['en' => 'Custom…',          'ta' => 'தனிப்பயன்',          'icon' => 'bi-pencil'],
    ],

    // ── Branding ───────────────────────────────────────────────────────────
    'brand_name' => 'AKSHATHAY',

];
