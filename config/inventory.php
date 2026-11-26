<?php

/*
 * Kitchen inventory control configuration.
 * Units: code => [label, dimension, factor to the dimension's base].
 * Only units in the same dimension convert automatically (kg<->gram, litre<->ml,
 * dozen->piece). Count-like units (packet, bag, box, bottle...) have their own
 * dimension and convert only through item-specific rules (inventory_unit_conversions).
 */
return [
    'units' => [
        'kg' => ['label' => 'Kilogram',   'dimension' => 'mass',   'factor' => 1000],
        'gram' => ['label' => 'Gram',       'dimension' => 'mass',   'factor' => 1],
        'litre' => ['label' => 'Litre',      'dimension' => 'volume', 'factor' => 1000],
        'ml' => ['label' => 'Millilitre', 'dimension' => 'volume', 'factor' => 1],
        'piece' => ['label' => 'Piece',      'dimension' => 'count',  'factor' => 1],
        'dozen' => ['label' => 'Dozen',      'dimension' => 'count',  'factor' => 12],
        'packet' => ['label' => 'Packet',     'dimension' => 'packet', 'factor' => 1],
        'bag' => ['label' => 'Bag',        'dimension' => 'bag',    'factor' => 1],
        'box' => ['label' => 'Box',        'dimension' => 'box',    'factor' => 1],
        'bottle' => ['label' => 'Bottle',     'dimension' => 'bottle', 'factor' => 1],
        'bundle' => ['label' => 'Bundle',     'dimension' => 'bundle', 'factor' => 1],
        'tin' => ['label' => 'Tin',        'dimension' => 'tin',    'factor' => 1],
        'cylinder' => ['label' => 'Cylinder',   'dimension' => 'cylinder', 'factor' => 1],
    ],

    // Spellings seen on invoices / typed by staff => unit code.
    'unit_aliases' => [
        'kgs' => 'kg', 'kilo' => 'kg', 'kilogram' => 'kg', 'kilograms' => 'kg',
        'g' => 'gram', 'gm' => 'gram', 'gms' => 'gram', 'grams' => 'gram', 'gr' => 'gram',
        'l' => 'litre', 'lt' => 'litre', 'ltr' => 'litre', 'ltrs' => 'litre', 'liter' => 'litre', 'litres' => 'litre', 'liters' => 'litre',
        'millilitre' => 'ml', 'mls' => 'ml',
        'pc' => 'piece', 'pcs' => 'piece', 'nos' => 'piece', 'no' => 'piece', 'pieces' => 'piece', 'each' => 'piece',
        'doz' => 'dozen', 'dzn' => 'dozen',
        'pkt' => 'packet', 'pkts' => 'packet', 'pack' => 'packet', 'packets' => 'packet',
        'bags' => 'bag', 'sack' => 'bag', 'boxes' => 'box', 'bottles' => 'bottle', 'btl' => 'bottle',
        'tins' => 'tin', 'can' => 'tin', 'bundles' => 'bundle', 'cyl' => 'cylinder',
    ],

    // Line limits for bulk grids.
    'max_lines' => 100,

    // Material request reconciliation: wastage above this % of issued needs admin review.
    'wastage_review_percent' => 10,
    // usage (used + wasted + unexplained) above the recipe standard / usual amount by more than this % goes to admin review
    'over_standard_review_percent' => 15,

    // Item matching: suggestions below this confidence are never pre-selected.
    'match_auto_select_confidence' => 85,
];
