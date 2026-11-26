<?php

/*
 | Curated starter recipes (hand-written, NOT AI-generated) for a South Indian catering kitchen.
 | All batches are for 100 people. Quantities are typical commercial-kitchen starting points and
 | MUST be checked by the kitchen before a recipe is verified — they are imported as "Curated starter — not verified".
 | Shape matches RecipeOutputValidator input. Import: php artisan recipes:import-starter (safe to re-run).
 */

$i = fn (string $name, ?float $qty, ?string $unit, ?string $note = null, bool $approx = false, bool $optional = false, ?string $display = null) => [
    'name' => $name, 'quantity' => $qty, 'unit' => $unit, 'prep_note' => $note, 'display_quantity' => $display,
    'approximate' => $approx, 'optional' => $optional,
];
$s = fn (string $title, string $text, ?int $min = null) => ['title' => $title, 'instruction' => $text, 'duration_minutes' => $min];
$r = fn (string $name, string $cat, string $cuisine, string $type, string $desc, int $prep, int $cook, array $ings, array $steps) => [
    'name' => $name, 'category' => $cat, 'cuisine' => $cuisine, 'food_type' => $type, 'description' => $desc,
    'yield_per_batch' => 100, 'yield_unit' => 'people', 'prep_time_minutes' => $prep, 'cook_time_minutes' => $cook,
    'ingredients' => $ings, 'steps' => $steps,
];
$temper = 'Heat the oil in a wide kadai on medium heat. Add mustard seeds and wait until they splutter, then add the dals and fry until light golden (about 30 seconds); add curry leaves last so they do not burn.';

return [
    // ── Breakfast ─────────────────────────────────────────────────────────
    $r('Idli', 'Breakfast', 'Tamil Nadu', 'veg', 'Soft steamed rice-and-urad cakes, about 4 per person.', 60, 40, [
        $i('Idli Rice', 6, 'kg', 'soaked 5–6 hours'), $i('Urad Dal', 1.5, 'kg', 'whole white, soaked 4 hours'),
        $i('Fenugreek Seeds', 30, 'gram', 'soaked with the urad dal'), $i('Salt', 120, 'gram'), $i('Sunflower Oil', 0.2, 'litre', 'for greasing plates', true),
    ], [
        $s('Soak', 'Wash the rice three times and soak 5–6 hours. Wash urad dal with fenugreek and soak 4 hours in separate vessels; keep both covered and cool.', 360),
        $s('Grind', 'Grind urad dal in the wet grinder with cold water added little by little for 25–30 minutes until light, fluffy and doubled in volume. Grind rice to a slightly coarse (rava-like) batter.', 45),
        $s('Mix and ferment', 'Combine both batters with salt by hand in a large clean food-grade container, filling it only half way. Cover and ferment 8–10 hours at room temperature until risen and pleasantly sour.', 540),
        $s('Steam', 'Grease idli plates, fill each mould three-quarters, and steam on high for 10–12 minutes until a skewer comes out clean. Rest 2 minutes before scooping out.', 12),
        $s('Hold and serve', 'Keep idlis covered in a steam-heated hot box above 65 °C and serve within 2 hours. Do not stack trays more than 4 high or the bottom layer turns soggy.'),
    ]),
    $r('Ven Pongal', 'Breakfast', 'Tamil Nadu', 'veg', 'Soft rice-moong dal pongal tempered with ghee, pepper and cumin.', 20, 40, [
        $i('Raw Rice', 4, 'kg', 'washed'), $i('Moong Dal', 1.5, 'kg', 'dry-roasted till aromatic'), $i('Ghee', 0.8, 'kg'),
        $i('Black Pepper', 60, 'gram', 'lightly crushed'), $i('Cumin Seeds', 60, 'gram'), $i('Ginger', 150, 'gram', 'finely chopped'),
        $i('Cashew Nuts', 250, 'gram', 'halves'), $i('Curry Leaves', 1, 'bundle'), $i('Salt', 120, 'gram'), $i('Water', 22, 'litre'),
    ], [
        $s('Roast dal', 'Dry-roast moong dal in a heavy pan on low-medium heat for 6–8 minutes until it smells nutty and turns pale golden; do not let it brown.', 8),
        $s('Cook rice and dal', 'Boil 22 L water in a thick-bottomed vessel, add washed rice and roasted dal, cook on medium with the lid partly open, stirring from the bottom every 5 minutes, for 30–35 minutes until fully soft and mashable.', 35),
        $s('Season', 'Mash lightly with a ladle, add salt and adjust the consistency with hot water — it should be soft and drop slowly from the ladle, as it thickens on standing.', 3),
        $s('Temper', 'Heat ghee on medium, fry cashews until golden, add pepper, cumin, ginger and curry leaves for 30 seconds until aromatic, and pour over the pongal. Mix well and hold covered above 65 °C.', 5),
    ]),
    $r('Rava Upma', 'Breakfast', 'Tamil Nadu', 'veg', 'Roasted semolina upma with onion, chilli and ginger.', 20, 30, [
        $i('Rava', 5, 'kg', 'Bombay sooji, dry-roasted'), $i('Sunflower Oil', 0.75, 'litre'), $i('Mustard Seeds', 50, 'gram'),
        $i('Urad Dal', 100, 'gram'), $i('Chana Dal', 100, 'gram'), $i('Onion', 2, 'kg', 'finely chopped'), $i('Green Chilli', 250, 'gram', 'slit'),
        $i('Ginger', 150, 'gram', 'finely chopped'), $i('Curry Leaves', 1, 'bundle'), $i('Salt', 120, 'gram'), $i('Water', 15, 'litre'),
    ], [
        $s('Roast rava', 'Dry-roast rava on low heat, stirring continuously, for 8–10 minutes until it is aromatic and grains look separate but not coloured. Spread on a tray to cool.', 10),
        $s('Temper', $temper.' Add ginger, green chilli and onion and sauté until the onion is translucent, about 5 minutes.', 8),
        $s('Boil water', 'Add 15 L water and salt to the tempering and bring to a rolling boil on high heat; taste — the water should be slightly salty.', 10),
        $s('Add rava', 'Lower to medium and pour rava in a thin stream with one cook stirring continuously to prevent lumps. Cover and cook on low for 5 minutes until the rava has absorbed the water and is soft. Fluff and hold above 65 °C.', 8),
    ]),
    $r('Medu Vadai', 'Breakfast', 'Tamil Nadu', 'veg', 'Crisp urad dal doughnuts, about 2 per person.', 30, 60, [
        $i('Urad Dal', 4, 'kg', 'soaked 3 hours'), $i('Onion', 1, 'kg', 'finely chopped'), $i('Green Chilli', 150, 'gram', 'finely chopped'),
        $i('Ginger', 100, 'gram', 'finely chopped'), $i('Black Pepper', 40, 'gram', 'crushed'), $i('Curry Leaves', 1, 'bundle', 'chopped'),
        $i('Salt', 80, 'gram'), $i('Sunflower Oil', 6, 'litre', 'for deep frying; about 1.5 L is absorbed', true),
    ], [
        $s('Grind', 'Drain the soaked dal well and grind with very little ice-cold water (sprinkled, not poured) for 20 minutes until fluffy and thick; a drop of batter should float in water.', 20),
        $s('Mix', 'Fold in onion, chilli, ginger, pepper, curry leaves and salt just before frying — salt added early makes the batter runny.', 5),
        $s('Fry', 'Heat oil to about 170 °C (a drop of batter rises at once). Shape vadais with wet hands, make the hole, and slide in. Fry on medium 4–5 minutes, turning once, until deep golden and crisp.', 50),
        $s('Drain and hold', 'Drain on perforated trays, not stacked, and hold in an open warmer; serve within 1 hour for crispness. Filter and discard oil that has turned dark or foamy.'),
    ]),
    $r('Chapati', 'Breakfast', 'North Indian', 'veg', 'Soft whole-wheat flatbreads, about 3 per person.', 45, 90, [
        $i('Wheat Flour', 8, 'kg', 'atta'), $i('Sunflower Oil', 0.6, 'litre'), $i('Salt', 100, 'gram'), $i('Water', 4.5, 'litre', 'lukewarm', true),
    ], [
        $s('Knead', 'Mix atta and salt, add lukewarm water gradually and knead 10 minutes to a soft, smooth dough that does not stick to the hand. Rub with a little oil and rest covered for 30 minutes.', 40),
        $s('Divide and roll', 'Divide into 35–40 g balls, dust lightly and roll into 6-inch rounds of even thickness; keep rolled chapatis covered so they do not dry out.', 40),
        $s('Cook', 'Cook on a hot tawa (medium-high) about 30 seconds until bubbles appear, flip, cook 30 seconds, then press the edges until it puffs. Brush lightly with oil.', 50),
        $s('Hold', 'Stack in a cloth-lined casserole or hot box and serve within 1 hour; do not seal hot chapatis in plastic, they turn soggy.'),
    ]),

    // ── Breakfast sides ──────────────────────────────────────────────────
    $r('Coconut Chutney', 'Side Dish', 'Tamil Nadu', 'veg', 'Fresh coconut chutney for idli, dosa and pongal.', 30, 10, [
        $i('Grated Coconut', 4, 'kg', 'about 12 coconuts'), $i('Roasted Gram', 800, 'gram', 'pottukadalai'), $i('Green Chilli', 200, 'gram'),
        $i('Ginger', 100, 'gram'), $i('Salt', 100, 'gram'), $i('Water', 4, 'litre', 'cold', true), $i('Sunflower Oil', 0.15, 'litre'),
        $i('Mustard Seeds', 30, 'gram'), $i('Urad Dal', 50, 'gram'), $i('Curry Leaves', 1, 'bundle'),
    ], [
        $s('Grind', 'Grind coconut, roasted gram, green chilli, ginger and salt in batches with cold water to a smooth, thick chutney; cold water keeps it from separating.', 20),
        $s('Temper', $temper.' Pour over the chutney and mix.', 5),
        $s('Hold safely', 'Coconut chutney spoils fast: keep it below 5 °C until service, serve within 2 hours of grinding and discard leftovers at the end of service.'),
    ]),
    $r('Tiffin Sambar', 'Side Dish', 'Tamil Nadu', 'veg', 'Thin small-onion sambar served with idli, vadai and pongal.', 20, 50, [
        $i('Toor Dal', 3, 'kg', 'washed'), $i('Small Onion', 3, 'kg', 'peeled'), $i('Tomato', 2, 'kg', 'chopped'), $i('Sambar Powder', 400, 'gram'),
        $i('Tamarind', 250, 'gram', 'soaked in 2 L warm water'), $i('Turmeric Powder', 30, 'gram'), $i('Sunflower Oil', 0.5, 'litre'),
        $i('Mustard Seeds', 40, 'gram'), $i('Asafoetida', 20, 'gram'), $i('Curry Leaves', 1, 'bundle'), $i('Coriander Leaves', 2, 'bundle', 'chopped'),
        $i('Salt', 250, 'gram'), $i('Water', 25, 'litre'),
    ], [
        $s('Cook dal', 'Pressure-cook toor dal with turmeric and 9 L water for 4–5 whistles until completely soft, then whisk smooth.', 25),
        $s('Cook onion and tomato', 'Heat oil, splutter mustard, add asafoetida and curry leaves, sauté small onions 6–8 minutes until soft and lightly golden, add tomato and cook until mushy.', 12),
        $s('Simmer', 'Add tamarind extract, sambar powder, salt and the remaining water; boil 10 minutes until the raw tamarind smell is gone. Add the dal and simmer 10 minutes — it should be pourable and slightly thin.', 20),
        $s('Finish', 'Add coriander leaves and hold covered above 65 °C; stir from the bottom before each serving.'),
    ]),

    // ── Rice ─────────────────────────────────────────────────────────────
    $r('Steamed Rice', 'Main Course', 'South Indian', 'veg', 'Plain boiled rice for meals, about 150 g raw rice per person.', 15, 35, [
        $i('Raw Rice', 15, 'kg', 'Ponni / sona masoori'), $i('Water', 45, 'litre', 'for open-pot boiling, drained', true), $i('Salt', 50, 'gram', null, false, true),
    ], [
        $s('Wash', 'Wash the rice 2–3 times until the water runs almost clear, and soak 20 minutes for even cooking.', 20),
        $s('Boil', 'Bring water to a rolling boil in a large vessel, add rice, stir once and cook uncovered on medium-high for 15–18 minutes. Check a grain: it should be soft with no white centre.', 18),
        $s('Drain and rest', 'Drain through a large colander, return to the vessel, cover and rest 10 minutes off heat so the grains firm up and separate. Hold above 65 °C.', 10),
    ]),
    $r('Lemon Rice', 'Main Course', 'Tamil Nadu', 'veg', 'Tangy tempered rice with peanuts.', 30, 40, [
        $i('Raw Rice', 10, 'kg', 'cooked grainy and cooled'), $i('Lemon', 60, 'piece', 'juiced, seeds removed', true), $i('Sunflower Oil', 1, 'litre'),
        $i('Mustard Seeds', 60, 'gram'), $i('Chana Dal', 250, 'gram'), $i('Urad Dal', 200, 'gram'), $i('Peanuts', 1, 'kg'),
        $i('Green Chilli', 300, 'gram', 'slit'), $i('Ginger', 200, 'gram', 'chopped'), $i('Turmeric Powder', 40, 'gram'),
        $i('Asafoetida', 20, 'gram'), $i('Curry Leaves', 2, 'bundle'), $i('Salt', 250, 'gram'),
    ], [
        $s('Cook rice', 'Cook the rice grainy (slightly firm), spread on trays and cool 15 minutes so the grains do not break when mixed.', 35),
        $s('Temper', 'Heat oil on medium, fry peanuts until crisp, then add mustard, chana dal and urad dal until golden; add chilli, ginger, curry leaves, asafoetida and turmeric for 30 seconds — do not let turmeric burn.', 8),
        $s('Mix', 'Take off heat, add lemon juice and salt to the tempering, pour over the rice and fold gently with a flat ladle until evenly yellow. Taste and adjust lemon and salt.', 10),
        $s('Hold', 'Hold covered at room temperature for up to 3 hours or above 65 °C; lemon rice is served warm, not hot.'),
    ]),
    $r('Tamarind Rice', 'Main Course', 'Tamil Nadu', 'veg', 'Puliyodarai — rice mixed with a cooked tamarind-spice paste.', 30, 60, [
        $i('Raw Rice', 10, 'kg', 'cooked grainy and cooled'), $i('Tamarind', 1.2, 'kg', 'soaked in 6 L warm water'), $i('Gingelly Oil', 1.5, 'litre'),
        $i('Dry Red Chilli', 200, 'gram'), $i('Peanuts', 1, 'kg'), $i('Chana Dal', 300, 'gram'), $i('Urad Dal', 200, 'gram'),
        $i('Mustard Seeds', 60, 'gram'), $i('Fenugreek Seeds', 40, 'gram', 'roasted and powdered'), $i('Turmeric Powder', 40, 'gram'),
        $i('Asafoetida', 30, 'gram'), $i('Jaggery', 300, 'gram'), $i('Curry Leaves', 2, 'bundle'), $i('Salt', 300, 'gram'),
    ], [
        $s('Extract tamarind', 'Squeeze the soaked tamarind and strain to a thick extract, about 6 L.', 15),
        $s('Make the paste', 'Heat oil, fry peanuts, then mustard, dals and red chilli until golden; add curry leaves, asafoetida and turmeric, then the tamarind extract, salt and jaggery. Cook on medium, stirring often, 30–35 minutes until thick and oil separates at the edges.', 40),
        $s('Mix', 'Add fenugreek powder, cool the paste slightly and fold into the cooled rice in batches with a flat ladle. Rest 30 minutes for the flavour to soak in.', 15),
    ]),
    $r('Curd Rice', 'Main Course', 'Tamil Nadu', 'veg', 'Soft rice with curd and a light tempering.', 20, 30, [
        $i('Raw Rice', 8, 'kg', 'cooked very soft'), $i('Curd', 12, 'kg', 'fresh, not sour'), $i('Milk', 6, 'litre', 'boiled and cooled'),
        $i('Ginger', 150, 'gram', 'finely chopped'), $i('Green Chilli', 200, 'gram', 'finely chopped'), $i('Sunflower Oil', 0.3, 'litre'),
        $i('Mustard Seeds', 40, 'gram'), $i('Urad Dal', 80, 'gram'), $i('Curry Leaves', 1, 'bundle'), $i('Coriander Leaves', 2, 'bundle', 'chopped'),
        $i('Salt', 200, 'gram'),
    ], [
        $s('Cook and cool rice', 'Cook rice with extra water until very soft, mash lightly and cool to room temperature (below 30 °C) — adding curd to hot rice makes it sour quickly.', 40),
        $s('Mix', 'Mix in curd, milk and salt until creamy; it thickens on standing, so keep it slightly loose.', 10),
        $s('Temper and chill', $temper.' Add ginger and chilli, pour over the rice, add coriander and mix. Keep below 8 °C and serve within 4 hours.', 5),
    ]),
    $r('Vegetable Biryani', 'Main Course', 'South Indian', 'veg', 'Dum-cooked basmati biryani with mixed vegetables.', 60, 70, [
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Carrot', 2, 'kg', 'diced'), $i('Beans', 2, 'kg', 'cut'), $i('Green Peas', 1.5, 'kg'),
        $i('Potato', 2, 'kg', 'cubed'), $i('Onion', 4, 'kg', 'sliced'), $i('Tomato', 2, 'kg', 'chopped'), $i('Ginger Garlic Paste', 600, 'gram'),
        $i('Curd', 2, 'kg', 'whisked'), $i('Mint Leaves', 4, 'bundle'), $i('Coriander Leaves', 4, 'bundle'), $i('Biryani Masala', 250, 'gram'),
        $i('Red Chilli Powder', 150, 'gram'), $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 0.5, 'kg'), $i('Cinnamon', 30, 'gram'),
        $i('Cloves', 20, 'gram'), $i('Cardamom', 20, 'gram'), $i('Bay Leaf', 20, 'gram'), $i('Salt', 350, 'gram'), $i('Water', 15, 'litre'),
    ], [
        $s('Masala', 'Heat oil and ghee, fry whole spices 30 seconds, add onions and fry 15 minutes on medium until golden brown. Add ginger-garlic paste until the raw smell goes, then tomato, chilli powder and biryani masala until oil separates.', 25),
        $s('Vegetables', 'Add vegetables, curd, half the mint and coriander and salt; cook 8–10 minutes until vegetables are half done.', 10),
        $s('Rice', 'Add 15 L water, bring to a boil, taste — it should be slightly over-salted — then add the drained rice and cook on high until most water is absorbed and holes appear on top.', 15),
        $s('Dum', 'Scatter the remaining herbs, seal the lid with dough or foil and cook on the lowest heat (or on a tawa) for 20 minutes. Rest 10 minutes and fluff gently from the sides. Hold above 65 °C.', 30),
    ]),

    // ── Gravies ──────────────────────────────────────────────────────────
    $r('Sambar', 'Main Course', 'Tamil Nadu', 'veg', 'Meal sambar with drumstick, brinjal and pumpkin.', 40, 60, [
        $i('Toor Dal', 3.5, 'kg', 'washed'), $i('Drumstick', 2, 'kg', 'cut in 3-inch pieces'), $i('Brinjal', 2, 'kg', 'cubed, kept in water'),
        $i('Pumpkin', 2, 'kg', 'cubed'), $i('Onion', 2, 'kg', 'chopped'), $i('Tomato', 2.5, 'kg', 'chopped'), $i('Tamarind', 500, 'gram', 'soaked'),
        $i('Sambar Powder', 600, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Sunflower Oil', 0.6, 'litre'), $i('Mustard Seeds', 40, 'gram'),
        $i('Fenugreek Seeds', 20, 'gram'), $i('Asafoetida', 20, 'gram'), $i('Curry Leaves', 2, 'bundle'), $i('Coriander Leaves', 2, 'bundle'),
        $i('Salt', 300, 'gram'), $i('Water', 30, 'litre'),
    ], [
        $s('Cook dal', 'Pressure-cook toor dal with turmeric and 10 L water until completely soft; whisk smooth.', 25),
        $s('Cook vegetables', 'Boil the vegetables in tamarind extract with sambar powder, salt and 10 L water on medium for 12–15 minutes until just tender — drumstick should pierce easily but hold its shape.', 15),
        $s('Combine', 'Add the dal and remaining water; simmer 10 minutes, stirring from the bottom so it does not catch. The sambar should coat the back of a ladle.', 10),
        $s('Temper and finish', 'Heat oil, splutter mustard and fenugreek, add onion, asafoetida and curry leaves, sauté until onion is soft, add tomato until mushy, pour into the sambar, add coriander. Hold above 65 °C.', 12),
    ]),
    $r('Rasam', 'Main Course', 'Tamil Nadu', 'veg', 'Thin pepper-tomato rasam for meals.', 20, 35, [
        $i('Tamarind', 600, 'gram', 'soaked in 5 L warm water'), $i('Tomato', 3, 'kg', 'crushed'), $i('Toor Dal', 0.6, 'kg', 'cooked soft'),
        $i('Rasam Powder', 300, 'gram'), $i('Black Pepper', 50, 'gram', 'crushed'), $i('Cumin Seeds', 50, 'gram', 'crushed'),
        $i('Garlic', 300, 'gram', 'crushed'), $i('Turmeric Powder', 20, 'gram'), $i('Ghee', 0.25, 'kg'), $i('Mustard Seeds', 30, 'gram'),
        $i('Asafoetida', 15, 'gram'), $i('Curry Leaves', 1, 'bundle'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 300, 'gram'), $i('Water', 25, 'litre'),
    ], [
        $s('Boil the base', 'Boil tamarind extract with tomato, turmeric, salt, garlic and rasam powder on medium for 12–15 minutes until the raw tamarind smell is gone.', 15),
        $s('Add dal water', 'Mash the cooked dal into the remaining water and add. Heat until it just starts to froth on top, then turn off — boiling rasam after this makes it bitter.', 8),
        $s('Temper', 'Heat ghee, splutter mustard, add pepper, cumin, asafoetida and curry leaves for 20 seconds, pour over the rasam, add coriander and cover at once to trap the aroma. Hold hot.', 5),
    ]),
    $r('Vegetable Kurma', 'Main Course', 'Tamil Nadu', 'veg', 'Coconut-cashew kurma for chapati, parotta and pulao.', 45, 45, [
        $i('Carrot', 2, 'kg', 'diced'), $i('Beans', 2, 'kg', 'cut'), $i('Potato', 3, 'kg', 'cubed'), $i('Green Peas', 1, 'kg'),
        $i('Onion', 2, 'kg', 'sliced'), $i('Tomato', 1.5, 'kg', 'chopped'), $i('Grated Coconut', 2, 'kg'), $i('Cashew Nuts', 300, 'gram', 'soaked'),
        $i('Fennel Seeds', 60, 'gram'), $i('Green Chilli', 250, 'gram'), $i('Ginger Garlic Paste', 400, 'gram'), $i('Sunflower Oil', 0.6, 'litre'),
        $i('Cinnamon', 20, 'gram'), $i('Cloves', 15, 'gram'), $i('Turmeric Powder', 20, 'gram'), $i('Salt', 250, 'gram'), $i('Water', 10, 'litre'),
    ], [
        $s('Paste', 'Grind coconut, cashew, fennel and green chilli with water to a very smooth paste.', 15),
        $s('Base', 'Heat oil, fry cinnamon and cloves, add onion and cook until soft and light golden, then ginger-garlic paste until the raw smell goes, then tomato and turmeric until soft.', 15),
        $s('Vegetables', 'Add vegetables, salt and water; cover and cook on medium 12–15 minutes until tender.', 15),
        $s('Finish', 'Stir in the coconut paste and simmer on low 8–10 minutes, stirring often, until it thickens and a thin film of oil appears; do not boil hard or the coconut splits. Hold above 65 °C.', 10),
    ]),
    $r('Channa Masala', 'Main Course', 'North Indian', 'veg', 'Chickpea gravy for chapati, poori and pulao.', 30, 60, [
        $i('Kabuli Chana', 5, 'kg', 'soaked overnight'), $i('Onion', 4, 'kg', 'finely chopped'), $i('Tomato', 4, 'kg', 'pureed'),
        $i('Ginger Garlic Paste', 500, 'gram'), $i('Chana Masala', 250, 'gram'), $i('Red Chilli Powder', 100, 'gram'), $i('Coriander Powder', 150, 'gram'),
        $i('Turmeric Powder', 30, 'gram'), $i('Sunflower Oil', 0.8, 'litre'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 250, 'gram'), $i('Water', 15, 'litre'),
    ], [
        $s('Cook chana', 'Pressure-cook soaked chana with salt and fresh water for 5–6 whistles until a chickpea mashes easily between two fingers. Keep the cooking water.', 35),
        $s('Masala', 'Heat oil, fry onion on medium 15 minutes until deep golden, add ginger-garlic paste, then the dry powders with a splash of water so they do not burn, then tomato puree; cook until oil separates.', 25),
        $s('Simmer', 'Add the chana with its cooking water and simmer 15 minutes, mashing a few chickpeas to thicken the gravy. Finish with coriander and hold above 65 °C.', 15),
    ]),

    // ── Vegetable sides ──────────────────────────────────────────────────
    $r('Cabbage Poriyal', 'Side Dish', 'Tamil Nadu', 'veg', 'Dry cabbage stir-fry with coconut.', 40, 25, [
        $i('Cabbage', 10, 'kg', 'finely shredded'), $i('Grated Coconut', 1.5, 'kg'), $i('Sunflower Oil', 0.4, 'litre'), $i('Mustard Seeds', 40, 'gram'),
        $i('Urad Dal', 80, 'gram'), $i('Green Chilli', 200, 'gram', 'slit'), $i('Curry Leaves', 1, 'bundle'), $i('Turmeric Powder', 20, 'gram'), $i('Salt', 150, 'gram'),
    ], [
        $s('Temper', $temper.' Add green chilli and turmeric.', 5),
        $s('Cook', 'Add cabbage and salt, toss well, cover and cook on medium 10–12 minutes, stirring every 3 minutes, until soft but still slightly crunchy — no water needed.', 12),
        $s('Finish', 'Add coconut, toss on high heat for 2 minutes to dry off moisture, and hold uncovered in a warmer so it does not sweat.', 3),
    ]),
    $r('Beans Poriyal', 'Side Dish', 'Tamil Nadu', 'veg', 'Finely chopped beans with coconut.', 45, 25, [
        $i('Beans', 8, 'kg', 'finely chopped'), $i('Grated Coconut', 1.2, 'kg'), $i('Sunflower Oil', 0.4, 'litre'), $i('Mustard Seeds', 40, 'gram'),
        $i('Urad Dal', 80, 'gram'), $i('Dry Red Chilli', 40, 'gram'), $i('Curry Leaves', 1, 'bundle'), $i('Salt', 140, 'gram'), $i('Water', 1, 'litre', null, true),
    ], [
        $s('Temper', $temper.' Add the red chillies.', 5),
        $s('Cook', 'Add beans, salt and a sprinkle of water, cover and cook on medium 12–15 minutes until tender and bright green; stir often so the bottom does not catch.', 15),
        $s('Finish', 'Mix in coconut and toss 2 minutes. Hold uncovered in a warmer.', 3),
    ]),
    $r('Potato Fry', 'Side Dish', 'Tamil Nadu', 'veg', 'Spicy roasted potato cubes.', 40, 40, [
        $i('Potato', 12, 'kg', 'boiled until just done, peeled and cubed'), $i('Sunflower Oil', 1.2, 'litre'), $i('Red Chilli Powder', 150, 'gram'),
        $i('Turmeric Powder', 30, 'gram'), $i('Mustard Seeds', 40, 'gram'), $i('Curry Leaves', 1, 'bundle'), $i('Asafoetida', 15, 'gram'), $i('Salt', 200, 'gram'),
    ], [
        $s('Boil', 'Boil potatoes in salted water until a knife goes in with slight resistance (about 15 minutes); cool, peel and cut into 1-inch cubes.', 30),
        $s('Roast', 'Heat oil in a wide pan, splutter mustard, add curry leaves and asafoetida, then potatoes in a single layer. Sprinkle chilli, turmeric and salt and roast on medium, turning gently every 3–4 minutes, for 15–20 minutes until crisp edges form.', 20),
        $s('Hold', 'Hold uncovered in a warmer; covering makes the crust soft.'),
    ]),
    $r('Avial', 'Side Dish', 'Kerala', 'veg', 'Mixed vegetables in coconut-curd with coconut oil.', 60, 30, [
        $i('Drumstick', 2, 'kg', 'batons'), $i('Raw Banana', 2, 'kg', 'batons'), $i('Carrot', 2, 'kg', 'batons'), $i('Beans', 2, 'kg', 'batons'),
        $i('Yam', 2, 'kg', 'batons'), $i('Ash Gourd', 2, 'kg', 'batons'), $i('Grated Coconut', 3, 'kg'), $i('Curd', 2, 'kg', 'slightly sour'),
        $i('Green Chilli', 250, 'gram'), $i('Cumin Seeds', 50, 'gram'), $i('Coconut Oil', 0.5, 'litre'), $i('Curry Leaves', 3, 'bundle'),
        $i('Turmeric Powder', 20, 'gram'), $i('Salt', 200, 'gram'), $i('Water', 3, 'litre'),
    ], [
        $s('Cook vegetables', 'Cook the harder vegetables (yam, raw banana, carrot) with turmeric, salt and water for 8 minutes, then add the rest and cook 6–8 minutes until tender but whole.', 15),
        $s('Coconut', 'Coarsely grind coconut, green chilli and cumin without much water and fold into the vegetables; cook on low 5 minutes.', 8),
        $s('Finish', 'Turn off the heat, fold in whisked curd, then pour raw coconut oil and curry leaves on top. Stir gently — avial should be thick, not watery. Hold warm, not boiling.', 3),
    ]),

    // ── Snacks ───────────────────────────────────────────────────────────
    $r('Onion Pakoda', 'Snacks', 'Tamil Nadu', 'veg', 'Crisp onion fritters for evening snacks.', 30, 60, [
        $i('Gram Flour', 3, 'kg', 'besan'), $i('Rice Flour', 0.75, 'kg'), $i('Onion', 6, 'kg', 'thinly sliced'), $i('Green Chilli', 200, 'gram', 'chopped'),
        $i('Ginger', 150, 'gram', 'chopped'), $i('Curry Leaves', 2, 'bundle'), $i('Red Chilli Powder', 80, 'gram'), $i('Salt', 120, 'gram'),
        $i('Sunflower Oil', 8, 'litre', 'for deep frying; about 2 L is absorbed', true),
    ], [
        $s('Mix', 'Rub the onion with salt, chilli, ginger and curry leaves and rest 10 minutes so it releases moisture. Add both flours and chilli powder and mix without adding water — sprinkle a little only if it does not bind.', 15),
        $s('Fry', 'Heat oil to about 170 °C. Drop loose, small clusters and fry on medium 5–6 minutes, turning, until deep golden and crisp.', 50),
        $s('Drain and hold', 'Drain on perforated trays in a single layer and serve within 45 minutes. Discard oil that smokes, foams or turns dark.'),
    ]),

    // ── Desserts ─────────────────────────────────────────────────────────
    $r('Rava Kesari', 'Dessert', 'Tamil Nadu', 'veg', 'Ghee semolina sweet, about 80 g per person.', 15, 40, [
        $i('Rava', 3, 'kg', 'Bombay sooji'), $i('Sugar', 5, 'kg'), $i('Ghee', 2, 'kg'), $i('Water', 9, 'litre'), $i('Cashew Nuts', 300, 'gram'),
        $i('Raisins', 200, 'gram'), $i('Cardamom', 30, 'gram', 'powdered'), $i('Kesari Colour', 5, 'gram', 'food grade, dissolved in water', true),
    ], [
        $s('Fry nuts and rava', 'Fry cashews and raisins in some ghee until golden and set aside. Roast rava in ghee on low 8–10 minutes until aromatic and sandy, not brown.', 15),
        $s('Cook', 'Boil water with the colour, add rava in a thin stream stirring constantly, cover and cook on low 5 minutes until it absorbs the water and is soft.', 10),
        $s('Sugar and ghee', 'Add sugar — the mixture will loosen — and stir on low until it thickens again and leaves the sides, adding the remaining ghee in parts. Finish with cardamom and nuts. Hold warm.', 15),
    ]),
    $r('Semiya Payasam', 'Dessert', 'Tamil Nadu', 'veg', 'Vermicelli milk payasam.', 15, 50, [
        $i('Vermicelli', 2, 'kg', 'roasted'), $i('Milk', 25, 'litre'), $i('Sugar', 4, 'kg'), $i('Ghee', 0.4, 'kg'), $i('Cashew Nuts', 300, 'gram'),
        $i('Raisins', 200, 'gram'), $i('Cardamom', 30, 'gram', 'powdered'), $i('Water', 4, 'litre'),
    ], [
        $s('Roast', 'Roast vermicelli in ghee on low until golden; fry cashews and raisins separately.', 10),
        $s('Cook', 'Boil milk with water in a heavy vessel, stirring from the bottom so it does not catch, then add vermicelli and cook on medium 8–10 minutes until soft.', 30),
        $s('Sweeten', 'Add sugar and simmer 5 minutes, then cardamom and nuts. Payasam thickens as it cools — keep it pourable with hot milk if needed. Hold above 65 °C or chill below 5 °C; never leave it lukewarm.', 8),
    ]),
    $r('Sakkarai Pongal', 'Dessert', 'Tamil Nadu', 'veg', 'Sweet jaggery pongal with ghee.', 20, 60, [
        $i('Raw Rice', 3, 'kg'), $i('Moong Dal', 1, 'kg', 'dry-roasted'), $i('Jaggery', 5, 'kg', 'melted and strained'), $i('Ghee', 1.5, 'kg'),
        $i('Milk', 4, 'litre'), $i('Water', 12, 'litre'), $i('Cashew Nuts', 300, 'gram'), $i('Raisins', 200, 'gram'), $i('Cardamom', 30, 'gram', 'powdered'),
        $i('Edible Camphor', 2, 'gram', 'a pinch', true, true),
    ], [
        $s('Cook rice and dal', 'Cook rice and roasted dal with milk and water for 30–35 minutes until very soft; mash well.', 35),
        $s('Jaggery', 'Melt jaggery in a little water, strain out grit, boil to a soft sticky syrup and stir into the rice. Cook on low 10 minutes, stirring, until it comes together.', 15),
        $s('Finish', 'Add ghee in parts, cardamom, camphor and fried nuts. Hold warm and covered.', 5),
    ]),

    // ── Beverages ────────────────────────────────────────────────────────
    $r('Filter Coffee', 'Beverage', 'Tamil Nadu', 'veg', 'South Indian filter coffee, 150 ml per cup.', 30, 20, [
        $i('Coffee Powder', 1.2, 'kg', 'filter blend with chicory'), $i('Water', 6, 'litre', 'for decoction'), $i('Milk', 12, 'litre'), $i('Sugar', 1.5, 'kg'),
    ], [
        $s('Decoction', 'Fill filters with coffee powder, press lightly, pour boiling water and let it drip 20–30 minutes into a thick decoction. Do not re-boil the decoction.', 30),
        $s('Milk', 'Boil milk in a heavy vessel and keep it just below boiling in a hot urn.', 15),
        $s('Mix', 'For each batch, mix 1 part decoction to 3 parts hot milk and sugar; froth by pouring between two jugs. Serve within 30 minutes — decoction older than 2 hours tastes bitter.'),
    ]),
    $r('Buttermilk', 'Beverage', 'Tamil Nadu', 'veg', 'Spiced buttermilk (neer mor), 200 ml per person.', 20, 0, [
        $i('Curd', 8, 'kg'), $i('Water', 16, 'litre', 'cold, potable'), $i('Green Chilli', 100, 'gram', 'crushed'), $i('Ginger', 100, 'gram', 'crushed'),
        $i('Curry Leaves', 1, 'bundle', 'torn'), $i('Coriander Leaves', 2, 'bundle', 'chopped'), $i('Asafoetida', 10, 'gram'), $i('Salt', 150, 'gram'),
    ], [
        $s('Blend', 'Whisk curd smooth, then add cold water gradually while whisking to avoid lumps.', 10),
        $s('Flavour', 'Add salt, crushed chilli, ginger, curry leaves, coriander and asafoetida; rest 15 minutes so the flavours infuse, then strain if serving in dispensers.', 15),
        $s('Hold cold', 'Keep below 8 °C and serve within 3 hours; use only potable water and clean, covered containers.'),
    ]),

    // ── Non-vegetarian ───────────────────────────────────────────────────
    $r('Chicken Biryani', 'Main Course', 'Tamil Nadu', 'non_veg', 'Dum chicken biryani, about 150 g chicken per person.', 90, 90, [
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Chicken', 15, 'kg', 'with bone, curry cut, washed'), $i('Onion', 5, 'kg', 'sliced'),
        $i('Tomato', 3, 'kg', 'chopped'), $i('Curd', 3, 'kg'), $i('Ginger Garlic Paste', 1, 'kg'), $i('Mint Leaves', 5, 'bundle'),
        $i('Coriander Leaves', 5, 'bundle'), $i('Red Chilli Powder', 200, 'gram'), $i('Turmeric Powder', 40, 'gram'), $i('Biryani Masala', 300, 'gram'),
        $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 1, 'kg'), $i('Cinnamon', 30, 'gram'), $i('Cloves', 20, 'gram'), $i('Cardamom', 20, 'gram'),
        $i('Bay Leaf', 20, 'gram'), $i('Lemon', 15, 'piece', 'juiced', true), $i('Salt', 450, 'gram'), $i('Water', 15, 'litre'),
    ], [
        $s('Marinate', 'Marinate chicken with curd, half the ginger-garlic paste, chilli, turmeric, lemon and salt for at least 1 hour in the chiller (below 5 °C). Use separate boards and knives for raw chicken and wash hands after handling.', 60),
        $s('Masala', 'Heat oil and ghee, fry whole spices, then onions on medium 15–20 minutes until deep golden. Add remaining ginger-garlic paste, tomato and biryani masala and cook until oil separates.', 25),
        $s('Cook chicken', 'Add the marinated chicken and half the herbs, cook covered on medium 20 minutes until the chicken is done (no pink at the bone, at least 75 °C in the thickest piece).', 20),
        $s('Rice and dum', 'Add water, boil, adjust salt to slightly salty, add rice and cook on high until the water is nearly absorbed. Scatter herbs, seal and dum on the lowest heat 20–25 minutes. Rest 10 minutes before opening; hold above 65 °C.', 40),
    ]),
];
