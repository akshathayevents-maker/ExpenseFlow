<?php

/*
 | Initial recipe library plan for the AI Recipe Generator. Each group becomes its own small batch
 | (dishes listed by name → no duplicates within the plan; names already in the library are skipped).
 | Extend freely: add dishes or groups; re-running the plan only generates what is missing.
 */
return [
    'max_per_batch' => 50,          // hard cap per batch (each dish = one AI request, run in the queue)
    'default_yield' => 100,
    'yields' => [25, 50, 100, 250, 500],

    'initial_plan' => [
        ['group' => 'Breakfast', 'category' => 'Breakfast', 'cuisine' => 'Tamil Nadu', 'food_type' => 'veg', 'dishes' => [
            'Idli', 'Plain Dosa', 'Ven Pongal', 'Rava Upma', 'Semiya Upma', 'Poori', 'Chapati', 'Parotta', 'Medu Vadai',
            'Masala Vadai', 'Rava Kichadi', 'Idiyappam', 'Pesarattu', 'Adai',
        ]],
        ['group' => 'Chutneys & breakfast sides', 'category' => 'Side Dish', 'cuisine' => 'Tamil Nadu', 'food_type' => 'veg', 'dishes' => [
            'Coconut Chutney', 'Tomato Chutney', 'Onion Chutney', 'Mint Chutney', 'Tiffin Sambar', 'Potato Masala for Poori',
            'Vegetable Saagu', 'Kurma for Parotta',
        ]],
        ['group' => 'Rice dishes', 'category' => 'Main Course', 'cuisine' => 'South Indian', 'food_type' => 'veg', 'dishes' => [
            'Steamed Rice', 'Lemon Rice', 'Tamarind Rice', 'Curd Rice', 'Coconut Rice', 'Vegetable Pulao', 'Tomato Rice',
            'Jeera Rice', 'Vegetable Biryani', 'Bisi Bele Bath', 'Sambar Rice',
        ]],
        ['group' => 'Gravies & kuzhambu', 'category' => 'Main Course', 'cuisine' => 'Tamil Nadu', 'food_type' => 'veg', 'dishes' => [
            'Sambar', 'Rasam', 'Dal Tadka', 'Vegetable Kurma', 'Vatha Kuzhambu', 'Mor Kuzhambu', 'Paneer Butter Masala',
            'Channa Masala', 'Kara Kuzhambu',
        ]],
        ['group' => 'Vegetable sides', 'category' => 'Side Dish', 'cuisine' => 'Tamil Nadu', 'food_type' => 'veg', 'dishes' => [
            'Cabbage Poriyal', 'Beans Poriyal', 'Carrot Poriyal', 'Beetroot Poriyal', 'Potato Fry', 'Avial', 'Kootu',
            'Keerai Masiyal', 'Vegetable Stew',
        ]],
        ['group' => 'Chicken', 'category' => 'Main Course', 'cuisine' => 'Chettinad', 'food_type' => 'non_veg', 'dishes' => [
            'Chicken Biryani', 'Chicken Chettinad Gravy', 'Pepper Chicken', 'Chicken 65', 'Chicken Kurma',
        ]],
        ['group' => 'Mutton & fish', 'category' => 'Main Course', 'cuisine' => 'Tamil Nadu', 'food_type' => 'non_veg', 'dishes' => [
            'Mutton Biryani', 'Mutton Kuzhambu', 'Mutton Sukka', 'Fish Curry', 'Fish Fry',
        ]],
        ['group' => 'Egg', 'category' => 'Main Course', 'cuisine' => 'South Indian', 'food_type' => 'egg', 'dishes' => [
            'Egg Curry', 'Egg Masala', 'Egg Podimas',
        ]],
        ['group' => 'Snacks', 'category' => 'Snacks', 'cuisine' => 'Tamil Nadu', 'food_type' => 'veg', 'dishes' => [
            'Onion Bajji', 'Potato Bonda', 'Onion Pakoda', 'Vegetable Samosa', 'Vegetable Cutlet', 'Sundal',
        ]],
        ['group' => 'Desserts', 'category' => 'Dessert', 'cuisine' => 'Tamil Nadu', 'food_type' => 'veg', 'dishes' => [
            'Semiya Payasam', 'Paal Payasam', 'Rava Kesari', 'Carrot Halwa', 'Sakkarai Pongal', 'Gulab Jamun',
        ]],
        ['group' => 'Beverages', 'category' => 'Beverage', 'cuisine' => 'South Indian', 'food_type' => 'veg', 'dishes' => [
            'Filter Coffee', 'Masala Tea', 'Buttermilk', 'Rose Milk',
        ]],
    ],
];
