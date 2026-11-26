<?php

/*
 | Curated (hand-written, not AI) bilingual recipes — non-vegetarian dishes, snacks/starters and desserts. 100 servings each.
 | Quantities are typical catering starting points: the kitchen must check them before verifying.
 */
$i = fn (string $name, ?float $qty, ?string $unit, ?string $note = null, bool $approx = false, bool $optional = false) => [
    'name' => $name, 'quantity' => $qty, 'unit' => $unit, 'prep_note' => $note, 'display_quantity' => null, 'approximate' => $approx, 'optional' => $optional,
];
$s = fn (string $title, string $titleTa, string $en, string $ta, ?int $min = null) => [
    'title' => $title, 'title_ta' => $titleTa, 'instruction' => $en, 'instruction_ta' => $ta, 'duration_minutes' => $min,
];
$r = fn (string $name, string $nameTa, string $cat, string $cuisine, string $type, string $desc, int $prep, int $cook, array $ings, array $steps, string $notes, string $notesTa) => [
    'name' => $name, 'name_ta' => $nameTa, 'category' => $cat, 'cuisine' => $cuisine, 'food_type' => $type, 'description' => $desc,
    'yield_per_batch' => 100, 'yield_unit' => 'people', 'prep_time_minutes' => $prep, 'cook_time_minutes' => $cook,
    'ingredients' => $ings, 'steps' => $steps, 'kitchen_notes' => $notes, 'kitchen_notes_ta' => $notesTa,
];
$meat = 'Raw meat/fish: separate board, knife and gloves; keep below 5 °C until cooking; cook until the thickest piece reaches 75 °C. For 250+ cook in vessels of up to 15 kg meat.';
$meatTa = 'பச்சை கறி/மீனுக்கு தனி பலகை, கத்தி, கையுறை; சமைக்கும் வரை 5 °C க்கு கீழ் வைக்கவும்; தடிமனான துண்டு 75 °C ஆகும் வரை வேக வைக்கவும். 250 பேருக்கு மேல் ஒரு பாத்திரத்தில் 15 கிலோ கறி வரை.';
$fry = 'Fry in small batches at 170–180 °C; overloading drops the oil temperature and makes food oily. Change oil when it darkens, foams or smokes.';
$fryTa = '170–180 °C இல் சிறு சிறு அளவில் பொரிக்கவும்; அதிகம் போட்டால் எண்ணெய் சூடு குறைந்து எண்ணெய் குடிக்கும். எண்ணெய் கருத்தால், நுரைத்தால் அல்லது புகைத்தால் மாற்றவும்.';

return [
    $r('Chicken Curry', 'சிக்கன் குழம்பு', 'Main Course', 'Tamil Nadu', 'non_veg', 'Home-style chicken curry with coconut, about 120 g chicken per person.', 40, 50, [
        $i('Chicken', 12, 'kg', 'with bone, curry cut, washed'), $i('Onion', 4, 'kg', 'sliced'), $i('Tomato', 3, 'kg', 'chopped'), $i('Ginger Garlic Paste', 700, 'gram'),
        $i('Grated Coconut', 1.5, 'kg', 'ground with fennel'), $i('Fennel Seeds', 40, 'gram'), $i('Red Chilli Powder', 200, 'gram'), $i('Coriander Powder', 250, 'gram'),
        $i('Turmeric Powder', 40, 'gram'), $i('Garam Masala', 60, 'gram'), $i('Sunflower Oil', 1, 'litre'), $i('Curry Leaves', 2, 'bundle'),
        $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 300, 'gram'), $i('Water', 10, 'litre'),
    ], [
        $s('Masala', 'மசாலா', 'Heat oil, add curry leaves and onion and fry until golden; add ginger-garlic paste until the raw smell goes, then tomato and the powders with a splash of water; cook until oil separates.', 'எண்ணெயில் கறிவேப்பிலை, வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது பச்சை வாசனை போகும் வரை, பின் தக்காளி, தூள்களை சிறிது தண்ணீருடன் சேர்த்து எண்ணெய் பிரியும் வரை வதக்கவும்.', 25),
        $s('Chicken', 'கோழிக்கறி', 'Add chicken and salt, stir on high 5 minutes to seal, add water, cover and cook on medium 20 minutes.', 'கறி, உப்பு சேர்த்து அதிக தீயில் 5 நிமிடம் கிளறி, தண்ணீர் சேர்த்து மூடி மிதமான தீயில் 20 நிமிடம் வேக விடவும்.', 25),
        $s('Coconut and finish', 'தேங்காய், முடித்தல்', 'Add the ground coconut-fennel paste and garam masala; simmer 8–10 minutes until the gravy coats a spoon and oil floats. Check: no pink at the bone. Add coriander, hold above 65 °C.', 'அரைத்த தேங்காய்-சோம்பு விழுது, கரம் மசாலா சேர்த்து 8–10 நிமிடம் — கரண்டியில் ஒட்டும் பதம், மேலே எண்ணெய் மிதக்கும் வரை கொதிக்க விடவும். எலும்பருகே பிங்க் இல்லை என சோதிக்கவும். கொத்தமல்லி சேர்த்து 65 °C க்கு மேல் வைக்கவும்.', 12),
    ], $meat, $meatTa),

    $r('Chicken Chettinad Gravy', 'செட்டிநாடு சிக்கன் கிரேவி', 'Main Course', 'Chettinad', 'non_veg', 'Spicy Chettinad gravy with freshly roasted masala; the standard chicken gravy for parotta and kuska.', 50, 50, [
        $i('Chicken', 12, 'kg', 'with bone'), $i('Small Onion', 3, 'kg', 'peeled'), $i('Tomato', 3, 'kg'), $i('Ginger Garlic Paste', 700, 'gram'),
        $i('Grated Coconut', 1, 'kg'), $i('Dry Red Chilli', 150, 'gram'), $i('Black Pepper', 80, 'gram'), $i('Fennel Seeds', 60, 'gram'), $i('Cumin Seeds', 40, 'gram'),
        $i('Coriander Powder', 150, 'gram'), $i('Star Anise', 10, 'gram'), $i('Cinnamon', 20, 'gram'), $i('Cloves', 15, 'gram'), $i('Turmeric Powder', 40, 'gram'),
        $i('Gingelly Oil', 1, 'litre'), $i('Curry Leaves', 3, 'bundle'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 300, 'gram'), $i('Water', 8, 'litre'),
    ], [
        $s('Roast masala', 'மசாலா வறுத்து அரைத்தல்', 'Dry-roast red chilli, pepper, fennel, cumin, star anise, cinnamon and cloves on low until aromatic; add coconut and roast until light brown. Cool and grind to a fine paste.', 'காய்ந்த மிளகாய், மிளகு, சோம்பு, சீரகம், அன்னாசிப்பூ, பட்டை, கிராம்பை குறைந்த தீயில் வாசனை வரும் வரை வறுத்து, தேங்காய் சேர்த்து லேசான பழுப்பாக வறுக்கவும். ஆறியதும் நைசாக அரைக்கவும்.', 20),
        $s('Base', 'அடிப்படை', 'Heat gingelly oil, add curry leaves and small onion until golden, then ginger-garlic paste, tomato, turmeric and coriander powder until oil separates.', 'நல்லெண்ணெயில் கறிவேப்பிலை, சின்ன வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, தக்காளி, மஞ்சள், மல்லித் தூள் எண்ணெய் பிரியும் வரை வதக்கவும்.', 20),
        $s('Cook', 'வேக வைத்தல்', 'Add chicken and salt, seal 5 minutes, add the ground masala and water; cover and cook 25 minutes on medium until thick and done at the bone. Finish with coriander.', 'கறி, உப்பு சேர்த்து 5 நிமிடம் கிளறி, அரைத்த மசாலா, தண்ணீர் சேர்த்து மூடி மிதமான தீயில் 25 நிமிடம் கெட்டியாகி எலும்பு வரை வேகும் வரை. கொத்தமல்லி தூவவும்.', 30),
    ], $meat.' Roast spices on low: burnt masala makes the whole batch bitter.', $meatTa.' மசாலாவை குறைந்த தீயில் வறுக்கவும்: கருகினால் முழு அளவும் கசக்கும்.'),

    $r('Pepper Chicken', 'மிளகு சிக்கன்', 'Main Course', 'Chettinad', 'non_veg', 'Dry pepper chicken roast, about 100 g boneless per person.', 40, 40, [
        $i('Boneless Chicken', 10, 'kg', 'cubed'), $i('Small Onion', 3, 'kg', 'sliced'), $i('Ginger Garlic Paste', 600, 'gram'), $i('Black Pepper', 250, 'gram', 'freshly crushed'),
        $i('Fennel Seeds', 60, 'gram', 'crushed'), $i('Turmeric Powder', 30, 'gram'), $i('Coriander Powder', 100, 'gram'), $i('Curry Leaves', 4, 'bundle'),
        $i('Sunflower Oil', 0.8, 'litre'), $i('Lemon', 10, 'piece', 'juiced', true), $i('Salt', 250, 'gram'),
    ], [
        $s('Marinate', 'ஊற வைத்தல்', 'Mix chicken with half the ginger-garlic paste, turmeric, salt and lemon; chill 30 minutes.', 'கறியை பாதி இஞ்சி பூண்டு விழுது, மஞ்சள், உப்பு, எலுமிச்சையுடன் கலந்து 30 நிமிடம் குளிர்சாதனத்தில் வைக்கவும்.', 30),
        $s('Cook', 'வேக வைத்தல்', 'Fry fennel, curry leaves and small onion in oil until golden, add the rest of the paste, then the chicken; cook covered on medium 15 minutes in its own juices.', 'எண்ணெயில் சோம்பு, கறிவேப்பிலை, சின்ன வெங்காயம் பொன்னிறமாக வதக்கி, மீதி விழுது, பின் கறி சேர்த்து மூடி மிதமான தீயில் அதன் சாற்றிலேயே 15 நிமிடம் வேக விடவும்.', 20),
        $s('Roast', 'வறுத்தல்', 'Open, add coriander powder and the crushed pepper and roast on high, stirring, until dry and coated (8–10 minutes). Check the centre of a large piece is white and hot.', 'திறந்து மல்லித் தூள், நசுக்கிய மிளகு சேர்த்து அதிக தீயில் கிளறி 8–10 நிமிடம் உலர்ந்து மசாலா ஒட்டும் வரை வறுக்கவும். பெரிய துண்டின் நடுவில் வெள்ளையாக சூடாக இருக்கிறதா பார்க்கவும்.', 10),
    ], $meat, $meatTa),

    $r('Chicken 65', 'சிக்கன் 65', 'Starter', 'South Indian', 'non_veg', 'Crisp spicy fried chicken starter, about 80 g boneless per person.', 60, 60, [
        $i('Boneless Chicken', 8, 'kg', 'bite-size'), $i('Ginger Garlic Paste', 400, 'gram'), $i('Kashmiri Chilli Powder', 200, 'gram'), $i('Turmeric Powder', 20, 'gram'),
        $i('Garam Masala', 50, 'gram'), $i('Curd', 1, 'kg'), $i('Corn Flour', 600, 'gram'), $i('Rice Flour', 300, 'gram'), $i('Egg', 10, 'piece', null, false, true),
        $i('Lemon', 10, 'piece', 'juiced', true), $i('Curry Leaves', 3, 'bundle'), $i('Green Chilli', 200, 'gram', 'slit'), $i('Salt', 180, 'gram'),
        $i('Sunflower Oil', 8, 'litre', 'for deep frying; about 1.5 L absorbed', true),
    ], [
        $s('Marinate', 'ஊற வைத்தல்', 'Mix chicken with ginger-garlic paste, chilli powder, turmeric, garam masala, curd, lemon, salt and egg; add both flours to coat. Chill at least 1 hour.', 'கறியை இஞ்சி பூண்டு விழுது, மிளகாய் தூள், மஞ்சள், கரம் மசாலா, தயிர், எலுமிச்சை, உப்பு, முட்டையுடன் கலந்து இரண்டு மாவும் சேர்த்து பிசறவும். குறைந்தது 1 மணி நேரம் குளிர்சாதனத்தில் வைக்கவும்.', 60),
        $s('Fry', 'பொரித்தல்', 'Heat oil to 175 °C, fry in small batches 5–6 minutes until deep red-golden and crisp; cut one piece — no pink inside.', 'எண்ணெயை 175 °C க்கு சூடாக்கி சிறு அளவில் 5–6 நிமிடம் சிவந்த பொன்னிறமாக மொறுமொறுப்பாக பொரிக்கவும்; ஒரு துண்டை வெட்டி பார்க்கவும் — உள்ளே பிங்க் இருக்கக் கூடாது.', 45),
        $s('Temper', 'தாளித்தல்', 'Fry curry leaves and green chilli in a little oil until crisp and toss with the chicken. Serve within 45 minutes.', 'சிறிது எண்ணெயில் கறிவேப்பிலை, பச்சை மிளகாய் மொறுமொறுப்பாக வறுத்து கறியுடன் கலக்கவும். 45 நிமிடத்துக்குள் பரிமாறவும்.', 5),
    ], $meat.' '.$fry, $meatTa.' '.$fryTa),

    $r('Chicken Fry', 'சிக்கன் வறுவல்', 'Side Dish', 'Tamil Nadu', 'non_veg', 'Dry masala chicken fry with bone.', 40, 45, [
        $i('Chicken', 10, 'kg', 'with bone, small pieces'), $i('Small Onion', 2, 'kg', 'sliced'), $i('Ginger Garlic Paste', 600, 'gram'), $i('Red Chilli Powder', 180, 'gram'),
        $i('Coriander Powder', 150, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Garam Masala', 60, 'gram'), $i('Black Pepper', 40, 'gram', 'crushed'),
        $i('Curry Leaves', 3, 'bundle'), $i('Sunflower Oil', 1, 'litre'), $i('Lemon', 10, 'piece', 'juiced', true), $i('Salt', 250, 'gram'), $i('Water', 2, 'litre', null, true),
    ], [
        $s('Marinate', 'ஊற வைத்தல்', 'Marinate chicken with ginger-garlic paste, powders, lemon and salt for 1 hour in the chiller.', 'கறியை இஞ்சி பூண்டு விழுது, தூள்கள், எலுமிச்சை, உப்புடன் குளிர்சாதனத்தில் 1 மணி நேரம் ஊற வைக்கவும்.', 60),
        $s('Cook', 'வேக வைத்தல்', 'Fry curry leaves and small onion in oil until golden, add the chicken and a little water; cover and cook 20 minutes on medium.', 'எண்ணெயில் கறிவேப்பிலை, சின்ன வெங்காயம் பொன்னிறமாக வதக்கி, கறியும் சிறிது தண்ணீரும் சேர்த்து மூடி மிதமான தீயில் 20 நிமிடம் வேக விடவும்.', 25),
        $s('Dry roast', 'வறுத்தல்', 'Open and roast on high, stirring, until the masala is dry and dark and clings to the pieces; finish with pepper.', 'திறந்து அதிக தீயில் கிளறி மசாலா உலர்ந்து கருப்பாகி துண்டுகளில் ஒட்டும் வரை வறுத்து மிளகு தூவவும்.', 15),
    ], $meat, $meatTa),

    $r('Egg Curry', 'முட்டை குழம்பு', 'Main Course', 'Tamil Nadu', 'egg', 'Boiled eggs in onion-tomato-coconut gravy, one egg per person.', 40, 35, [
        $i('Egg', 100, 'piece', 'boiled 10 minutes, peeled'), $i('Onion', 3, 'kg', 'sliced'), $i('Tomato', 3, 'kg'), $i('Ginger Garlic Paste', 400, 'gram'),
        $i('Grated Coconut', 1, 'kg', 'ground'), $i('Red Chilli Powder', 150, 'gram'), $i('Coriander Powder', 200, 'gram'), $i('Turmeric Powder', 30, 'gram'),
        $i('Garam Masala', 50, 'gram'), $i('Sunflower Oil', 0.8, 'litre'), $i('Curry Leaves', 2, 'bundle'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 250, 'gram'),
        $i('Water', 8, 'litre'),
    ], [
        $s('Eggs', 'முட்டை', 'Boil eggs from cold water 10 minutes, cool in water, peel and make 2 shallow slits on each.', 'முட்டையை குளிர்ந்த தண்ணீரில் இருந்து 10 நிமிடம் வேக வைத்து தண்ணீரில் ஆற வைத்து தோல் உரித்து ஒவ்வொன்றிலும் 2 லேசான கீறல் போடவும்.', 25),
        $s('Gravy', 'கிரேவி', 'Fry curry leaves and onion in oil until golden, add ginger-garlic paste, tomato and powders until oil separates; add water, salt and coconut paste and simmer 10 minutes.', 'எண்ணெயில் கறிவேப்பிலை, வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, தக்காளி, தூள்கள் எண்ணெய் பிரியும் வரை வதக்கவும்; தண்ணீர், உப்பு, தேங்காய் விழுது சேர்த்து 10 நிமிடம் கொதிக்க விடவும்.', 25),
        $s('Eggs in', 'முட்டை சேர்த்தல்', 'Add eggs and garam masala and simmer 5 minutes so the eggs absorb the gravy; add coriander. Hold above 65 °C.', 'முட்டை, கரம் மசாலா சேர்த்து 5 நிமிடம் கொதிக்க விடவும் — முட்டையில் சுவை ஏறும்; கொத்தமல்லி சேர்த்து 65 °C க்கு மேல் வைக்கவும்.', 5),
    ], 'Count eggs into each serving vessel so every portion gets one. Discard cracked raw eggs.', 'ஒவ்வொருவருக்கும் ஒன்று கிடைக்கும்படி பாத்திரத்துக்கு எண்ணி வைக்கவும். உடைந்த பச்சை முட்டையை எறியவும்.'),

    $r('Egg Masala', 'முட்டை மசாலா', 'Side Dish', 'South Indian', 'egg', 'Thick dry egg masala for chapati and rice.', 40, 30, [
        $i('Egg', 100, 'piece', 'boiled, halved'), $i('Onion', 4, 'kg', 'finely chopped'), $i('Tomato', 2.5, 'kg', 'finely chopped'), $i('Ginger Garlic Paste', 300, 'gram'),
        $i('Green Chilli', 150, 'gram'), $i('Red Chilli Powder', 120, 'gram'), $i('Coriander Powder', 120, 'gram'), $i('Turmeric Powder', 25, 'gram'),
        $i('Garam Masala', 50, 'gram'), $i('Black Pepper', 30, 'gram', 'crushed'), $i('Sunflower Oil', 0.7, 'litre'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 200, 'gram'),
    ], [
        $s('Masala', 'மசாலா', 'Fry onion and green chilli until golden, add ginger-garlic paste, tomato and powders; cook until thick and oil separates.', 'வெங்காயம், பச்சை மிளகாய் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, தக்காளி, தூள்கள் சேர்த்து கெட்டியாகி எண்ணெய் பிரியும் வரை வதக்கவும்.', 20),
        $s('Eggs', 'முட்டை', 'Place the halved eggs yolk-side up, spoon masala over, cover and heat 5 minutes on low without stirring hard.', 'இரண்டாக நறுக்கிய முட்டையை மஞ்சள் கரு மேலே இருக்கும்படி வைத்து, மசாலாவை மேலே போட்டு மூடி குறைந்த தீயில் 5 நிமிடம் — பலமாக கிளற வேண்டாம்.', 8),
        $s('Finish', 'முடித்தல்', 'Sprinkle pepper, garam masala and coriander. Hold above 65 °C.', 'மிளகு, கரம் மசாலா, கொத்தமல்லி தூவி 65 °C க்கு மேல் வைக்கவும்.', 2),
    ], 'Cut eggs with a wet knife so yolks stay whole; fold gently.', 'ஈரக் கத்தியால் வெட்டினால் மஞ்சள் கரு உடையாது; மெதுவாக கலக்கவும்.'),

    $r('Mutton Curry', 'மட்டன் கறி', 'Main Course', 'Tamil Nadu', 'non_veg', 'Thick mutton curry, about 120 g mutton per person.', 50, 90, [
        $i('Mutton', 12, 'kg', 'with bone, washed'), $i('Onion', 4, 'kg', 'sliced'), $i('Tomato', 3, 'kg'), $i('Ginger Garlic Paste', 800, 'gram'), $i('Curd', 1, 'kg'),
        $i('Red Chilli Powder', 200, 'gram'), $i('Coriander Powder', 250, 'gram'), $i('Turmeric Powder', 40, 'gram'), $i('Garam Masala', 80, 'gram'),
        $i('Fennel Seeds', 40, 'gram'), $i('Sunflower Oil', 1, 'litre'), $i('Curry Leaves', 2, 'bundle'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 300, 'gram'),
        $i('Water', 10, 'litre'),
    ], [
        $s('Marinate', 'ஊற வைத்தல்', 'Marinate mutton with curd, half the ginger-garlic paste, turmeric and salt for 1–2 hours in the chiller.', 'மட்டனை தயிர், பாதி இஞ்சி பூண்டு விழுது, மஞ்சள், உப்புடன் குளிர்சாதனத்தில் 1–2 மணி நேரம் ஊற வைக்கவும்.', 90),
        $s('Masala', 'மசாலா', 'Fry fennel, curry leaves and onion until deep golden, add the rest of the paste, tomato and powders until oil separates.', 'சோம்பு, கறிவேப்பிலை, வெங்காயம் நன்றாக பொன்னிறமாக வதக்கி, மீதி விழுது, தக்காளி, தூள்கள் எண்ணெய் பிரியும் வரை வதக்கவும்.', 25),
        $s('Cook', 'வேக வைத்தல்', 'Add mutton, seal 5 minutes, add water and pressure-cook 6–7 whistles (or simmer covered 75–90 minutes) until a piece pulls off the bone easily. Reduce to a thick curry, add garam masala and coriander.', 'மட்டன் சேர்த்து 5 நிமிடம் கிளறி, தண்ணீர் சேர்த்து குக்கரில் 6–7 விசில் (அல்லது மூடி 75–90 நிமிடம்) — கறி எலும்பில் இருந்து எளிதாக பிரிய வேண்டும். கெட்டியாக சுண்ட விட்டு கரம் மசாலா, கொத்தமல்லி சேர்க்கவும்.', 75),
    ], $meat, $meatTa),

    $r('Mutton Kuzhambu', 'மட்டன் குழம்பு', 'Main Course', 'Tamil Nadu', 'non_veg', 'Thinner Tamil-style mutton gravy for rice, about 100 g mutton per person.', 50, 90, [
        $i('Mutton', 10, 'kg', 'with bone, small pieces'), $i('Small Onion', 3, 'kg', 'peeled'), $i('Tomato', 3, 'kg'), $i('Ginger Garlic Paste', 600, 'gram'),
        $i('Grated Coconut', 1.5, 'kg'), $i('Fennel Seeds', 50, 'gram'), $i('Black Pepper', 40, 'gram'), $i('Red Chilli Powder', 180, 'gram'),
        $i('Coriander Powder', 250, 'gram'), $i('Turmeric Powder', 40, 'gram'), $i('Gingelly Oil', 1, 'litre'), $i('Curry Leaves', 3, 'bundle'),
        $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 320, 'gram'), $i('Water', 18, 'litre'),
    ], [
        $s('Cook mutton', 'மட்டன் வேக வைத்தல்', 'Pressure-cook mutton with turmeric, salt, half the ginger-garlic paste and 8 L water until tender (6–7 whistles). Keep the stock.', 'மட்டனை மஞ்சள், உப்பு, பாதி இஞ்சி பூண்டு விழுது, 8 லிட்டர் தண்ணீருடன் குக்கரில் 6–7 விசில் மென்மையாக வேக விடவும். சாற்றை வைத்துக்கொள்ளவும்.', 50),
        $s('Masala', 'மசாலா', 'Grind coconut, fennel and pepper to a paste. Fry small onion and curry leaves in gingelly oil until golden, add the remaining paste, tomato, chilli and coriander powders until oil separates.', 'தேங்காய், சோம்பு, மிளகு விழுதாக அரைக்கவும். நல்லெண்ணெயில் சின்ன வெங்காயம், கறிவேப்பிலை பொன்னிறமாக வதக்கி, மீதி இஞ்சி பூண்டு விழுது, தக்காளி, மிளகாய், மல்லித் தூள் எண்ணெய் பிரியும் வரை வதக்கவும்.', 25),
        $s('Simmer', 'கொதிக்க விடுதல்', 'Add mutton with stock and the rest of the water, then the coconut paste; simmer 15 minutes until oil floats. Add coriander; hold above 65 °C.', 'மட்டனை சாற்றுடனும் மீதி தண்ணீருடனும், பின் தேங்காய் விழுது சேர்த்து 15 நிமிடம் மேலே எண்ணெய் மிதக்கும் வரை கொதிக்க விடவும். கொத்தமல்லி சேர்த்து 65 °C க்கு மேல் வைக்கவும்.', 15),
    ], $meat, $meatTa),

    $r('Fish Curry', 'மீன் குழம்பு', 'Main Course', 'Tamil Nadu', 'non_veg', 'Tangy tamarind fish curry, about 100 g fish per person.', 40, 40, [
        $i('Fish', 10, 'kg', 'seer or similar, cleaned steaks'), $i('Small Onion', 2, 'kg', 'peeled'), $i('Tomato', 3, 'kg'), $i('Tamarind', 700, 'gram', 'soaked'),
        $i('Garlic', 400, 'gram', 'peeled'), $i('Red Chilli Powder', 200, 'gram'), $i('Coriander Powder', 250, 'gram'), $i('Turmeric Powder', 40, 'gram'),
        $i('Fenugreek Seeds', 30, 'gram'), $i('Mustard Seeds', 30, 'gram'), $i('Grated Coconut', 1, 'kg', 'ground', false, true), $i('Gingelly Oil', 1, 'litre'),
        $i('Curry Leaves', 3, 'bundle'), $i('Salt', 300, 'gram'), $i('Water', 12, 'litre'),
    ], [
        $s('Clean fish', 'மீன் சுத்தம்', 'Wash fish in cold water, rub with turmeric and salt, rinse again and keep on ice until it goes into the curry.', 'மீனை குளிர்ந்த தண்ணீரில் கழுவி மஞ்சள், உப்பு தடவி மீண்டும் கழுவி, குழம்பில் போடும் வரை ஐஸில் வைக்கவும்.', 20),
        $s('Gravy', 'குழம்பு', 'Splutter mustard and fenugreek in gingelly oil, add curry leaves, small onion and garlic until golden, then tomato and powders; add tamarind extract, water and salt and boil 15 minutes until the raw tamarind smell goes.', 'நல்லெண்ணெயில் கடுகு, வெந்தயம் வெடித்ததும் கறிவேப்பிலை, சின்ன வெங்காயம், பூண்டு பொன்னிறமாக வதக்கி, தக்காளி, தூள்கள் சேர்க்கவும்; புளிக்கரைசல், தண்ணீர், உப்பு சேர்த்து 15 நிமிடம் புளி வாசனை போகும் வரை கொதிக்க விடவும்.', 25),
        $s('Fish in', 'மீன் சேர்த்தல்', 'Add coconut paste if using, then slide in the fish and simmer gently 8–10 minutes without stirring (swirl the vessel instead) until the fish flakes. Hold above 65 °C.', 'தேங்காய் விழுது (இருந்தால்) சேர்த்து, மீனை மெதுவாக போட்டு கிளறாமல் (பாத்திரத்தை சுழற்றவும்) 8–10 நிமிடம் மீன் உதிரும் வரை மெதுவாக கொதிக்க விடவும். 65 °C க்கு மேல் வைக்கவும்.', 10),
    ], $meat.' Buy fish on the day; reject fish with dull eyes, grey gills or a strong smell.', $meatTa.' மீனை அன்றே வாங்கவும்; மங்கிய கண், சாம்பல் செவுள் அல்லது துர்நாற்றம் உள்ள மீனை வாங்க வேண்டாம்.'),

    $r('Fish Fry', 'மீன் வறுவல்', 'Starter', 'Tamil Nadu', 'non_veg', 'Masala-coated shallow-fried fish, one piece (about 80 g) per person.', 45, 60, [
        $i('Fish', 8, 'kg', 'seer or vanjaram slices'), $i('Red Chilli Powder', 200, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Ginger Garlic Paste', 300, 'gram'),
        $i('Rice Flour', 300, 'gram'), $i('Lemon', 15, 'piece', 'juiced', true), $i('Curry Leaves', 2, 'bundle'), $i('Salt', 150, 'gram'),
        $i('Sunflower Oil', 3, 'litre', 'for shallow frying; about 1 L absorbed', true),
    ], [
        $s('Marinate', 'ஊற வைத்தல்', 'Make a thick paste of chilli, turmeric, ginger-garlic paste, rice flour, lemon and salt; coat the fish and chill 30 minutes.', 'மிளகாய் தூள், மஞ்சள், இஞ்சி பூண்டு விழுது, அரிசி மாவு, எலுமிச்சை, உப்பு கெட்டியான விழுதாக்கி மீனில் தடவி 30 நிமிடம் குளிர்சாதனத்தில் வைக்கவும்.', 30),
        $s('Fry', 'பொரித்தல்', 'Shallow-fry on a hot tawa or flat pan on medium, 3–4 minutes each side, until crisp and the flesh is opaque to the bone.', 'சூடான தோசைக்கல் அல்லது தட்டையான வாணலியில் மிதமான தீயில் ஒவ்வொரு பக்கமும் 3–4 நிமிடம் மொறுமொறுப்பாகி சதை எலும்பு வரை வெள்ளையாகும் வரை பொரிக்கவும்.', 50),
        $s('Garnish', 'அலங்கரித்தல்', 'Fry curry leaves crisp in the same oil and scatter over. Serve within 30 minutes.', 'அதே எண்ணெயில் கறிவேப்பிலையை மொறுமொறுப்பாக வறுத்து மேலே தூவவும். 30 நிமிடத்துக்குள் பரிமாறவும்.', 5),
    ], $meat.' '.$fry, $meatTa.' '.$fryTa),

    // ── snacks & starters ───────────────────────────────────────────────
    $r('Vegetable Pakoda', 'காய்கறி பக்கோடா', 'Snacks', 'South Indian', 'veg', 'Mixed vegetable fritters.', 40, 60, [
        $i('Gram Flour', 3, 'kg'), $i('Rice Flour', 0.75, 'kg'), $i('Onion', 3, 'kg', 'sliced'), $i('Cabbage', 2, 'kg', 'shredded'), $i('Carrot', 1, 'kg', 'grated'),
        $i('Capsicum', 0.5, 'kg', 'thin strips'), $i('Green Chilli', 200, 'gram'), $i('Ginger', 150, 'gram'), $i('Curry Leaves', 2, 'bundle'), $i('Red Chilli Powder', 80, 'gram'),
        $i('Salt', 140, 'gram'), $i('Sunflower Oil', 8, 'litre', 'for deep frying; about 2 L absorbed', true),
    ], [
        $s('Mix', 'கலத்தல்', 'Rub vegetables with salt, chilli, ginger and curry leaves; rest 10 minutes. Add both flours and chilli powder and mix without water (sprinkle only if needed).', 'காய்களை உப்பு, மிளகாய், இஞ்சி, கறிவேப்பிலையுடன் பிசறி 10 நிமிடம் வைக்கவும். இரண்டு மாவும் மிளகாய் தூளும் சேர்த்து தண்ணீர் இல்லாமல் (தேவையெனில் மட்டும் தெளித்து) கலக்கவும்.', 15),
        $s('Fry', 'பொரித்தல்', 'Drop loose clusters into oil at 170 °C and fry 5–6 minutes, turning, until golden and crisp.', '170 °C எண்ணெயில் தளர்வான கொத்துகளாக போட்டு திருப்பி 5–6 நிமிடம் பொன்னிறமாக மொறுமொறுப்பாக பொரிக்கவும்.', 50),
        $s('Serve', 'பரிமாறுதல்', 'Drain in a single layer and serve within 45 minutes.', 'ஒரே அடுக்கில் வடித்து 45 நிமிடத்துக்குள் பரிமாறவும்.', 0),
    ], $fry, $fryTa),

    $r('Vegetable Samosa', 'வெஜிடபிள் சமோசா', 'Snacks', 'North Indian', 'veg', 'Potato-pea samosa, one large or two small per person.', 120, 90, [
        $i('Maida', 4, 'kg'), $i('Ghee', 0.6, 'kg', 'for the dough'), $i('Potato', 8, 'kg', 'boiled, diced'), $i('Green Peas', 1.5, 'kg'), $i('Onion', 1, 'kg', 'chopped'),
        $i('Green Chilli', 150, 'gram'), $i('Ginger', 150, 'gram'), $i('Cumin Seeds', 50, 'gram'), $i('Coriander Powder', 80, 'gram'), $i('Red Chilli Powder', 60, 'gram'),
        $i('Garam Masala', 50, 'gram'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 180, 'gram'), $i('Sunflower Oil', 10, 'litre', 'for frying; about 2 L absorbed', true),
        $i('Water', 1.6, 'litre', null, true),
    ], [
        $s('Dough', 'மாவு', 'Rub ghee and salt into maida until it holds when pressed; add water gradually to a stiff dough and rest 30 minutes covered.', 'மைதாவில் நெய், உப்பு சேர்த்து அழுத்தினால் சேரும் வரை பிசறி, தண்ணீரை கொஞ்சம் கொஞ்சமாக சேர்த்து கெட்டியான மாவாக பிசைந்து மூடி 30 நிமிடம் வைக்கவும்.', 40),
        $s('Filling', 'பூரணம்', 'Splutter cumin in oil, add onion, ginger and chilli, then peas, potato, powders and salt; mash lightly, cool completely and add coriander.', 'எண்ணெயில் சீரகம் வெடித்ததும் வெங்காயம், இஞ்சி, மிளகாய், பின் பட்டாணி, உருளை, தூள்கள், உப்பு சேர்த்து லேசாக மசித்து முழுமையாக ஆற விட்டு கொத்தமல்லி சேர்க்கவும்.', 25),
        $s('Shape', 'மடித்தல்', 'Roll ovals, cut in half, form cones, fill and seal the edges with water; keep shaped samosas covered.', 'நீள்வட்டமாக தேய்த்து இரண்டாக வெட்டி கூம்பு செய்து பூரணம் நிரப்பி ஓரங்களை தண்ணீரால் ஒட்டவும்; மடித்தவற்றை மூடி வைக்கவும்.', 60),
        $s('Fry', 'பொரித்தல்', 'Fry in oil at 150–160 °C (lower than usual) for 10–12 minutes until blistered and golden — hot oil browns the outside before the pastry cooks.', '150–160 °C (வழக்கத்தை விட குறைந்த) எண்ணெயில் 10–12 நிமிடம் பொன்னிறமாக பொரிக்கவும் — அதிக சூட்டில் உள்ளே வேகும் முன் வெளியே கருக்கும்.', 60),
    ], $fry, $fryTa),

    $r('Paneer 65', 'பனீர் 65', 'Starter', 'South Indian', 'veg', 'Crisp spicy fried paneer starter.', 40, 45, [
        $i('Paneer', 6, 'kg', 'cubed'), $i('Kashmiri Chilli Powder', 150, 'gram'), $i('Ginger Garlic Paste', 300, 'gram'), $i('Curd', 0.6, 'kg'), $i('Corn Flour', 500, 'gram'),
        $i('Rice Flour', 300, 'gram'), $i('Garam Masala', 40, 'gram'), $i('Curry Leaves', 3, 'bundle'), $i('Green Chilli', 150, 'gram'), $i('Salt', 120, 'gram'),
        $i('Sunflower Oil', 6, 'litre', 'for deep frying; about 1 L absorbed', true),
    ], [
        $s('Coat', 'பிசறுதல்', 'Mix paneer gently with chilli powder, ginger-garlic paste, curd, garam masala, salt and both flours until every cube is coated; rest 20 minutes.', 'பனீரை மிளகாய் தூள், இஞ்சி பூண்டு விழுது, தயிர், கரம் மசாலா, உப்பு, இரண்டு மாவுடன் எல்லா துண்டிலும் ஒட்டும்படி மெதுவாக பிசறி 20 நிமிடம் வைக்கவும்.', 25),
        $s('Fry', 'பொரித்தல்', 'Fry at 175 °C in small batches for 2–3 minutes until crisp — longer frying makes paneer tough.', '175 °C இல் சிறு அளவில் 2–3 நிமிடம் மொறுமொறுப்பாக பொரிக்கவும் — அதிக நேரம் பொரித்தால் பனீர் கடினமாகும்.', 35),
        $s('Temper', 'தாளித்தல்', 'Toss with crisp-fried curry leaves and green chilli. Serve within 30 minutes.', 'மொறுமொறுப்பாக வறுத்த கறிவேப்பிலை, பச்சை மிளகாயுடன் கலக்கவும். 30 நிமிடத்துக்குள் பரிமாறவும்.', 5),
    ], $fry, $fryTa),

    $r('Gobi 65', 'கோபி 65', 'Starter', 'South Indian', 'veg', 'Crisp fried cauliflower starter.', 40, 50, [
        $i('Cauliflower', 10, 'kg', 'florets, soaked in warm salt water'), $i('Kashmiri Chilli Powder', 180, 'gram'), $i('Ginger Garlic Paste', 300, 'gram'),
        $i('Corn Flour', 700, 'gram'), $i('Rice Flour', 400, 'gram'), $i('Garam Masala', 40, 'gram'), $i('Curry Leaves', 3, 'bundle'), $i('Green Chilli', 150, 'gram'),
        $i('Salt', 150, 'gram'), $i('Sunflower Oil', 8, 'litre', 'for deep frying; about 1.5 L absorbed', true),
    ], [
        $s('Blanch', 'வேக வைத்தல்', 'Soak florets in warm salt water 10 minutes, then blanch in boiling water 2 minutes; drain and dry well.', 'காலிஃபிளவரை வெதுவெதுப்பான உப்புத் தண்ணீரில் 10 நிமிடம் ஊற வைத்து, கொதிக்கும் தண்ணீரில் 2 நிமிடம் போட்டு வடித்து நன்றாக உலர விடவும்.', 20),
        $s('Coat and fry', 'பிசறி பொரித்தல்', 'Coat with chilli powder, ginger-garlic paste, garam masala, salt and both flours with a sprinkle of water; fry at 175 °C for 4–5 minutes until crisp.', 'மிளகாய் தூள், இஞ்சி பூண்டு விழுது, கரம் மசாலா, உப்பு, இரண்டு மாவும் சிறிது தண்ணீர் தெளித்து பிசறி 175 °C இல் 4–5 நிமிடம் மொறுமொறுப்பாக பொரிக்கவும்.', 40),
        $s('Temper', 'தாளித்தல்', 'Toss with fried curry leaves and green chilli; serve within 30 minutes.', 'வறுத்த கறிவேப்பிலை, பச்சை மிளகாயுடன் கலந்து 30 நிமிடத்துக்குள் பரிமாறவும்.', 5),
    ], $fry, $fryTa),

    $r('French Fries', 'ஃப்ரெஞ்ச் ஃப்ரைஸ்', 'Snacks', 'Continental', 'veg', 'Double-fried potato fries, about 100 g per person.', 60, 60, [
        $i('Potato', 14, 'kg', 'peeled, cut in batons'), $i('Salt', 120, 'gram'), $i('Pepper Powder', 30, 'gram', null, false, true),
        $i('Sunflower Oil', 10, 'litre', 'for frying; about 1.5 L absorbed', true),
    ], [
        $s('Cut and soak', 'நறுக்கி ஊற வைத்தல்', 'Cut potatoes into even batons, soak in cold water 30 minutes to remove starch, drain and dry completely on cloths.', 'உருளையை சம அளவு நீளத் துண்டுகளாக நறுக்கி மாவுச்சத்து போக 30 நிமிடம் குளிர்ந்த தண்ணீரில் ஊற வைத்து, வடித்து துணியில் முழுமையாக உலர விடவும்.', 45),
        $s('First fry', 'முதல் பொரிப்பு', 'Fry at 150–160 °C for 5–6 minutes until soft but pale; drain and cool on trays.', '150–160 °C இல் 5–6 நிமிடம் மென்மையாக, நிறம் மாறாமல் பொரித்து தட்டுகளில் வடித்து ஆற விடவும்.', 30),
        $s('Second fry', 'இரண்டாம் பொரிப்பு', 'Close to serving, fry again at 180 °C for 2–3 minutes until golden and crisp; salt immediately and serve within 15 minutes.', 'பரிமாறும் நேரத்துக்கு அருகில் 180 °C இல் 2–3 நிமிடம் பொன்னிறமாக மொறுமொறுப்பாக மீண்டும் பொரித்து உடனே உப்பு தூவி 15 நிமிடத்துக்குள் பரிமாறவும்.', 20),
    ], $fry.' Wet potatoes make hot oil spit — dry them fully.', $fryTa.' ஈரமான உருளை சூடான எண்ணெயில் தெறிக்கும் — முழுமையாக உலர வைக்கவும்.'),

    $r('Vegetable Cutlet', 'வெஜிடபிள் கட்லெட்', 'Snacks', 'South Indian', 'veg', 'Crumbed potato-vegetable cutlets, two per person.', 90, 60, [
        $i('Potato', 6, 'kg', 'boiled, mashed'), $i('Carrot', 1.5, 'kg', 'grated'), $i('Beans', 1, 'kg', 'finely chopped'), $i('Green Peas', 1, 'kg'), $i('Beetroot', 0.5, 'kg', 'grated', false, true),
        $i('Onion', 1, 'kg', 'finely chopped'), $i('Green Chilli', 150, 'gram'), $i('Ginger', 100, 'gram'), $i('Garam Masala', 60, 'gram'), $i('Red Chilli Powder', 50, 'gram'),
        $i('Maida', 1, 'kg', 'for slurry'), $i('Bread Crumbs', 2, 'kg'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 150, 'gram'),
        $i('Sunflower Oil', 5, 'litre', 'for shallow or deep frying', true), $i('Water', 2, 'litre', 'for slurry', true),
    ], [
        $s('Mix', 'கலவை', 'Cook onion, ginger, chilli and the vegetables in a little oil until dry; mix into mashed potato with spices, salt and coriander; cool completely.', 'சிறிது எண்ணெயில் வெங்காயம், இஞ்சி, மிளகாய், காய்கறிகளை ஈரம் சுண்டும் வரை வதக்கி, மசித்த உருளையுடன் மசாலா, உப்பு, கொத்தமல்லி சேர்த்து கலந்து முழுமையாக ஆற விடவும்.', 30),
        $s('Shape and crumb', 'வடிவம், பிரெட் தூள்', 'Shape 50 g oval cutlets, dip in maida slurry and roll in bread crumbs; chill 20 minutes so they hold shape.', '50 கிராம் நீள்வட்ட கட்லெட் செய்து மைதா கரைசலில் நனைத்து பிரெட் தூளில் புரட்டி, உருவம் கலையாமல் இருக்க 20 நிமிடம் குளிர்சாதனத்தில் வைக்கவும்.', 45),
        $s('Fry', 'பொரித்தல்', 'Shallow-fry on a tawa or deep-fry at 170 °C until golden on both sides; drain and serve hot with ketchup or mint chutney.', 'தோசைக்கல்லில் லேசாக அல்லது 170 °C எண்ணெயில் இருபுறமும் பொன்னிறமாக பொரித்து வடித்து கெட்சப் அல்லது புதினா சட்னியுடன் சூடாக பரிமாறவும்.', 45),
    ], $fry.' Cutlets can be shaped and crumbed the day before and kept chilled.', $fryTa.' கட்லெட்டை முந்தைய நாளே செய்து பிரெட் தூள் புரட்டி குளிர்ச்சியாக வைக்கலாம்.'),

    // ── desserts ─────────────────────────────────────────────────────────
    $r('Pal Payasam', 'பால் பாயசம்', 'Dessert', 'Tamil Nadu', 'veg', 'Slow-cooked rice and milk payasam (rice payasam), about 150 ml per person.', 15, 90, [
        $i('Raw Rice', 1.2, 'kg', 'washed'), $i('Milk', 25, 'litre'), $i('Sugar', 3.5, 'kg'), $i('Ghee', 0.3, 'kg'), $i('Cashew Nuts', 300, 'gram'),
        $i('Raisins', 200, 'gram'), $i('Cardamom', 30, 'gram', 'powdered'),
    ], [
        $s('Cook rice in milk', 'பாலில் அரிசி வேக வைத்தல்', 'Bring milk to a boil in a heavy vessel, add the rice and cook on low for 60–75 minutes, stirring from the bottom every few minutes, until the rice is soft and the milk has reduced by about a third.', 'கனமான பாத்திரத்தில் பாலை கொதிக்க வைத்து அரிசி சேர்த்து குறைந்த தீயில் 60–75 நிமிடம், சில நிமிடத்துக்கு ஒரு முறை அடியில் இருந்து கிளறி, அரிசி குழைந்து பால் மூன்றில் ஒரு பங்கு சுண்டும் வரை வேக விடவும்.', 75),
        $s('Sweeten', 'இனிப்பு சேர்த்தல்', 'Add sugar and simmer 10 minutes until pale pink-cream and thick.', 'சர்க்கரை சேர்த்து 10 நிமிடம் வெளிர் இளஞ்சிவப்பு-கிரீம் நிறமாகி கெட்டியாகும் வரை கொதிக்க விடவும்.', 10),
        $s('Finish', 'முடித்தல்', 'Add cardamom and ghee-fried nuts. Hold above 65 °C or chill below 5 °C.', 'ஏலக்காய், நெய்யில் வறுத்த நட்ஸ் சேர்க்கவும். 65 °C க்கு மேல் அல்லது 5 °C க்கு கீழ் வைக்கவும்.', 5),
    ], 'Use a heavy or double-bottomed vessel and never leave it unstirred — burnt milk cannot be saved. For 250+ use two vessels.', 'கனமான அல்லது இரட்டை அடிப்பாத்திரம்; கிளறாமல் விட வேண்டாம் — அடிபிடித்த பாலை சரிசெய்ய முடியாது. 250 பேருக்கு மேல் இரண்டு பாத்திரங்கள்.'),

    $r('Gulab Jamun', 'குலோப் ஜாமூன்', 'Dessert', 'North Indian', 'veg', 'Fried khoya dumplings in cardamom syrup, two per person.', 60, 90, [
        $i('Khoya', 3, 'kg', 'unsweetened, crumbled'), $i('Maida', 0.6, 'kg'), $i('Baking Soda', 10, 'gram'), $i('Milk', 0.6, 'litre', 'to bind', true),
        $i('Sugar', 7, 'kg', 'for syrup'), $i('Water', 6, 'litre', 'for syrup'), $i('Cardamom', 25, 'gram', 'crushed'), $i('Rose Water', 0.05, 'litre', null, false, true),
        $i('Ghee', 3, 'kg', 'for frying (or oil)', true),
    ], [
        $s('Syrup', 'பாகு', 'Boil sugar and water 8–10 minutes to a light sticky syrup (not thread); add cardamom and rose water and keep warm.', 'சர்க்கரை, தண்ணீரை 8–10 நிமிடம் லேசான பிசுபிசுப்பான பாகாக (கம்பிப் பாகு அல்ல) கொதிக்க வைத்து ஏலக்காய், ரோஸ் வாட்டர் சேர்த்து வெதுவெதுப்பாக வைக்கவும்.', 15),
        $s('Dough', 'மாவு', 'Mash khoya smooth, add maida and soda, bind with a little milk into a soft, crack-free dough; do not knead hard.', 'கோவாவை நைசாக மசித்து மைதா, சோடா சேர்த்து சிறிது பாலுடன் மென்மையான, வெடிப்பில்லாத மாவாக சேர்க்கவும்; அழுத்தி பிசைய வேண்டாம்.', 20),
        $s('Shape', 'உருண்டை', 'Roll 20 g balls between palms until completely smooth — cracks open up in the oil.', 'உள்ளங்கையில் 20 கிராம் உருண்டைகளாக வெடிப்பு இல்லாமல் மென்மையாக உருட்டவும் — வெடிப்பு எண்ணெயில் பிளக்கும்.', 30),
        $s('Fry and soak', 'பொரித்து ஊற வைத்தல்', 'Fry in ghee on low heat (about 140 °C), moving gently, for 8–10 minutes until deep brown all over; drop into warm syrup and soak at least 1 hour.', 'நெய்யில் குறைந்த தீயில் (சுமார் 140 °C) மெதுவாக அசைத்து 8–10 நிமிடம் எல்லா பக்கமும் அடர் பழுப்பாக பொரித்து வெதுவெதுப்பான பாகில் போட்டு குறைந்தது 1 மணி நேரம் ஊற விடவும்.', 60),
    ], 'Fry a test ball first: if it cracks, add a little milk to the dough; if it flattens, add a little maida. Keep jamuns in syrup covered; serve warm or at room temperature same day.', 'முதலில் ஒரு உருண்டையை சோதித்து பொரிக்கவும்: வெடித்தால் சிறிது பால், தட்டையானால் சிறிது மைதா சேர்க்கவும். ஜாமூனை பாகில் மூடி வைத்து அன்றே வெதுவெதுப்பாக அல்லது அறை வெப்பத்தில் பரிமாறவும்.'),

    $r('Fruit Salad', 'ஃப்ரூட் சாலட்', 'Dessert', 'Continental', 'veg', 'Chilled mixed fruit in sweetened milk custard-style base, about 150 g per person.', 60, 15, [
        $i('Apple', 3, 'kg', 'diced'), $i('Banana', 3, 'kg', 'sliced just before mixing'), $i('Grapes', 2, 'kg', 'halved'), $i('Pineapple', 3, 'kg', 'diced'),
        $i('Pomegranate', 1.5, 'kg', 'seeds'), $i('Milk', 8, 'litre'), $i('Sugar', 1.5, 'kg'), $i('Condensed Milk', 1, 'kg', null, false, true), $i('Corn Flour', 0.25, 'kg', 'mixed in cold milk'),
        $i('Cardamom', 15, 'gram', 'powdered'),
    ], [
        $s('Milk base', 'பால் அடிப்படை', 'Boil milk with sugar, stir in the corn-flour slurry and cook 3–4 minutes until it coats a spoon; add cardamom and condensed milk and chill below 5 °C.', 'பாலை சர்க்கரையுடன் கொதிக்க வைத்து சோள மாவு கரைசல் சேர்த்து 3–4 நிமிடம் கரண்டியில் ஒட்டும் பதம் வரை கிளறி, ஏலக்காய், கண்டென்ஸ்டு மில்க் சேர்த்து 5 °C க்கு கீழ் குளிர்விக்கவும்.', 20),
        $s('Fruit', 'பழங்கள்', 'Wash all fruit in potable water, cut on a clean (vegetable-only) board and keep chilled; slice banana and apple last so they do not brown.', 'எல்லா பழங்களையும் குடிநீரில் கழுவி சுத்தமான (காய்கறிக்கு மட்டுமான) பலகையில் நறுக்கி குளிர்ச்சியாக வைக்கவும்; பழுப்பாகாமல் இருக்க வாழை, ஆப்பிளை கடைசியில் நறுக்கவும்.', 40),
        $s('Combine and hold cold', 'கலந்து குளிர்ச்சியாக வைத்தல்', 'Fold fruit into the cold base close to service and keep the serving bowl on ice; serve within 2 hours.', 'பரிமாறும் நேரத்துக்கு அருகில் பழங்களை குளிர்ந்த பாலில் கலந்து பரிமாறும் கிண்ணத்தை ஐஸ் மேல் வைக்கவும்; 2 மணி நேரத்துக்குள் பரிமாறவும்.', 10),
    ], 'Raw fruit and milk: everything cold, cut fruit with clean hands/gloves, and discard leftovers after service.', 'பச்சை பழமும் பாலும்: எல்லாம் குளிர்ச்சியாக, சுத்தமான கை/கையுறையுடன் நறுக்கவும், சேவைக்குப் பிறகு மீதியை எறியவும்.'),
];
