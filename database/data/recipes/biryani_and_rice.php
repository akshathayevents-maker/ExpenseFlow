<?php

/*
 | Curated (hand-written, not AI) bilingual recipes — biryani varieties and rice dishes. 100 servings each.
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
$spices = fn () => [$i('Cinnamon', 30, 'gram'), $i('Cloves', 20, 'gram'), $i('Cardamom', 20, 'gram'), $i('Bay Leaf', 20, 'gram'), $i('Star Anise', 10, 'gram')];
$meatSafety = 'Raw meat: separate board, knife and gloves; marinate only in the chiller (below 5 °C). Cook until the thickest piece reaches 75 °C. For 250+ cook in separate vessels of 10 kg rice; dum time stays the same per vessel.';
$meatSafetyTa = 'பச்சை கறிக்கு தனி பலகை, கத்தி, கையுறை; குளிர்சாதனத்தில் (5 °C க்கு கீழ்) மட்டும் ஊற வைக்கவும். தடிமனான துண்டு 75 °C ஆகும் வரை வேக வைக்கவும். 250 பேருக்கு மேல் 10 கிலோ அரிசி அளவில் தனி பாத்திரங்கள்; ஒவ்வொன்றுக்கும் டம் நேரம் அதேதான்.';
$dumEn = 'Scatter mint and coriander, seal the lid with dough or foil and cook on the lowest heat (or on a tawa) for 20–25 minutes. Rest 10 minutes, then lift gently from the sides with a flat ladle so grains stay long. Hold above 65 °C.';
$dumTa = 'புதினா, கொத்தமல்லி தூவி மூடியை மாவு அல்லது அலுமினிய பேப்பரால் சீல் செய்து மிகக் குறைந்த தீயில் (அல்லது தோசைக்கல் மேல்) 20–25 நிமிடம் டம் போடவும். 10 நிமிடம் கழித்து தட்டையான கரண்டியால் ஓரத்தில் இருந்து மெதுவாக கிளறவும் — பருக்கை உடையக் கூடாது. 65 °C க்கு மேல் வைக்கவும்.';

return [
    $r('Chicken Seeraga Samba Biryani', 'சிக்கன் சீரக சம்பா பிரியாணி', 'Main Course', 'Tamil Nadu', 'non_veg', 'Dindigul-style biryani with short-grain seeraga samba rice.', 90, 90, array_merge([
        $i('Seeraga Samba Rice', 10, 'kg', 'washed, soaked 20 minutes'), $i('Chicken', 15, 'kg', 'with bone, small pieces'), $i('Small Onion', 4, 'kg', 'peeled, sliced'),
        $i('Tomato', 2.5, 'kg', 'chopped'), $i('Curd', 2, 'kg'), $i('Ginger Garlic Paste', 1, 'kg'), $i('Green Chilli', 300, 'gram', 'slit'),
        $i('Mint Leaves', 5, 'bundle'), $i('Coriander Leaves', 5, 'bundle'), $i('Red Chilli Powder', 150, 'gram'), $i('Turmeric Powder', 30, 'gram'),
        $i('Black Pepper', 60, 'gram', 'crushed'), $i('Fennel Seeds', 60, 'gram'), $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 1, 'kg'),
        $i('Lemon', 15, 'piece', 'juiced', true), $i('Salt', 450, 'gram'), $i('Water', 15, 'litre', 'about 1.5 L per kg rice', true),
    ], $spices()), [
        $s('Marinate', 'ஊற வைத்தல்', 'Marinate chicken with curd, half the ginger-garlic paste, chilli powder, turmeric, lemon and salt; keep in the chiller for 1 hour.', 'கோழிக்கறியை தயிர், பாதி இஞ்சி பூண்டு விழுது, மிளகாய் தூள், மஞ்சள், எலுமிச்சை, உப்புடன் கலந்து குளிர்சாதனத்தில் 1 மணி நேரம் வைக்கவும்.', 60),
        $s('Masala', 'மசாலா', 'Heat oil and ghee, fry whole spices and fennel 30 seconds, add small onion and green chilli and fry until golden. Add remaining ginger-garlic paste, then tomato and pepper; cook until oil separates.', 'எண்ணெய், நெய்யில் முழு மசாலா, சோம்பு 30 வினாடி வறுத்து, சின்ன வெங்காயம், பச்சை மிளகாய் பொன்னிறமாக வதக்கவும். மீதி இஞ்சி பூண்டு விழுது, தக்காளி, மிளகு சேர்த்து எண்ணெய் பிரியும் வரை வதக்கவும்.', 25),
        $s('Cook chicken', 'கறி வேக வைத்தல்', 'Add the chicken with half the mint and coriander, cover and cook on medium for 20 minutes until no pink remains at the bone.', 'கறியும் பாதி புதினா கொத்தமல்லியும் சேர்த்து மூடி மிதமான தீயில் 20 நிமிடம் — எலும்பருகே பிங்க் நிறம் இருக்கக் கூடாது.', 20),
        $s('Rice and dum', 'அரிசி, டம்', 'Add water, boil and make it slightly salty, add the drained seeraga samba rice and cook on high until the water is nearly absorbed (seeraga samba cooks faster than basmati — check after 8 minutes). '.$dumEn, 'தண்ணீர் கொதிக்க வைத்து சற்று அதிக உப்பாக்கி, வடித்த சீரக சம்பா அரிசி சேர்த்து அதிக தீயில் தண்ணீர் சுண்டும் வரை வேக விடவும் (சீரக சம்பா பாஸ்மதியை விட சீக்கிரம் வேகும் — 8 நிமிடத்தில் பார்க்கவும்). '.$dumTa, 40),
    ], $meatSafety, $meatSafetyTa),

    $r('Chicken Hyderabadi Biryani', 'சிக்கன் ஹைதராபாத் பிரியாணி', 'Main Course', 'North Indian', 'non_veg', 'Layered kachchi-style biryani with saffron-ghee rice.', 120, 90, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Chicken', 15, 'kg', 'with bone, large pieces'), $i('Onion', 6, 'kg', 'thin sliced, deep-fried golden'),
        $i('Curd', 4, 'kg'), $i('Ginger Garlic Paste', 1, 'kg'), $i('Green Chilli', 300, 'gram', 'paste'), $i('Mint Leaves', 6, 'bundle'), $i('Coriander Leaves', 6, 'bundle'),
        $i('Red Chilli Powder', 150, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Garam Masala', 120, 'gram'), $i('Ghee', 1.5, 'kg'),
        $i('Sunflower Oil', 3, 'litre', 'for frying onions; about 1 L absorbed', true), $i('Milk', 1, 'litre', 'with saffron or kesari colour', true),
        $i('Lemon', 20, 'piece', 'juiced', true), $i('Salt', 500, 'gram'), $i('Water', 50, 'litre', 'for par-boiling rice, drained', true),
    ], $spices()), [
        $s('Marinate', 'ஊற வைத்தல்', 'Mix chicken with curd, ginger-garlic paste, chilli paste, chilli powder, turmeric, garam masala, half the fried onion, half the herbs, lemon and salt. Marinate 2–4 hours in the chiller.', 'கோழிக்கறியை தயிர், இஞ்சி பூண்டு விழுது, மிளகாய் விழுது, மிளகாய் தூள், மஞ்சள், கரம் மசாலா, பாதி பொரித்த வெங்காயம், பாதி புதினா கொத்தமல்லி, எலுமிச்சை, உப்புடன் கலந்து குளிர்சாதனத்தில் 2–4 மணி நேரம் ஊற வைக்கவும்.', 180),
        $s('Par-boil rice', 'அரிசி அரை வேக்காடு', 'Boil plenty of salted water with whole spices, add rice and cook 6–7 minutes until 70% done (grain breaks with a hard centre). Drain immediately.', 'முழு மசாலா சேர்த்த உப்புத் தண்ணீரை நன்றாக கொதிக்க வைத்து அரிசி சேர்த்து 6–7 நிமிடம் 70% வேக விடவும் (பருக்கையை உடைத்தால் நடுவில் கெட்டியாக இருக்கும்). உடனே வடிக்கவும்.', 15),
        $s('Layer', 'அடுக்குதல்', 'Spread the marinated chicken in a thick-bottomed vessel with some ghee, cover with the par-boiled rice, then sprinkle fried onion, herbs, saffron milk and the remaining ghee.', 'கனமான பாத்திரத்தில் நெய் தடவி ஊறிய கறியை பரப்பி, அதன் மேல் அரை வெந்த அரிசியை போட்டு, பொரித்த வெங்காயம், புதினா கொத்தமல்லி, கேசரி பால், மீதி நெய் தூவவும்.', 15),
        $s('Dum', 'டம்', 'Seal tightly. Cook on high 10 minutes until steam escapes, then on the lowest heat (on a tawa) 40–45 minutes so the raw chicken cooks in the steam. Check one piece at the bottom: no pink, 75 °C. Rest 10 minutes and serve from the side so each portion has rice and chicken.', 'நன்றாக சீல் செய்யவும். ஆவி வெளியேறும் வரை அதிக தீயில் 10 நிமிடம், பின் மிகக் குறைந்த தீயில் (தோசைக்கல் மேல்) 40–45 நிமிடம் — பச்சை கறி ஆவியில் வெந்துவிடும். அடியில் ஒரு துண்டு பார்க்கவும்: பிங்க் இல்லை, 75 °C. 10 நிமிடம் கழித்து ஓரத்தில் இருந்து அரிசியும் கறியும் சேர்த்து பரிமாறவும்.', 55),
    ], $meatSafety.' Kachchi dum needs a tight seal and full time — never shorten it.', $meatSafetyTa.' பச்சை கறி டம்முக்கு இறுக்கமான சீலும் முழு நேரமும் அவசியம் — குறைக்க வேண்டாம்.'),

    $r('Chicken Donne Biryani', 'சிக்கன் தொன்னை பிரியாணி', 'Main Course', 'Karnataka', 'non_veg', 'Bengaluru-style green masala biryani with short-grain rice.', 90, 80, array_merge([
        $i('Seeraga Samba Rice', 10, 'kg', 'soaked 20 minutes'), $i('Chicken', 15, 'kg', 'with bone'), $i('Onion', 4, 'kg', 'sliced'), $i('Tomato', 1.5, 'kg'),
        $i('Curd', 2, 'kg'), $i('Mint Leaves', 8, 'bundle'), $i('Coriander Leaves', 8, 'bundle'), $i('Green Chilli', 500, 'gram'), $i('Ginger', 400, 'gram'),
        $i('Garlic', 400, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 0.8, 'kg'), $i('Lemon', 15, 'piece', 'juiced', true),
        $i('Salt', 450, 'gram'), $i('Water', 15, 'litre', null, true),
    ], $spices()), [
        $s('Green paste', 'பச்சை விழுது', 'Grind mint, coriander, green chilli, ginger and garlic to a smooth green paste with very little water.', 'புதினா, கொத்தமல்லி, பச்சை மிளகாய், இஞ்சி, பூண்டு சிறிது தண்ணீருடன் நைசாக பச்சை விழுதாக அரைக்கவும்.', 15),
        $s('Masala', 'மசாலா', 'Fry whole spices in oil and ghee, add onion and fry until light golden (not dark), add the green paste and cook 8–10 minutes until it loses its raw smell, then tomato and turmeric.', 'எண்ணெய், நெய்யில் முழு மசாலா வறுத்து, வெங்காயம் லேசான பொன்னிறமாக (கருக்காமல்) வதக்கி, பச்சை விழுது சேர்த்து 8–10 நிமிடம் பச்சை வாசனை போகும் வரை, பின் தக்காளி, மஞ்சள் சேர்க்கவும்.', 25),
        $s('Chicken', 'கறி', 'Add chicken, curd and salt; cover and cook on medium for 20 minutes until done at the bone.', 'கறி, தயிர், உப்பு சேர்த்து மூடி மிதமான தீயில் 20 நிமிடம் எலும்பு வரை வேக விடவும்.', 20),
        $s('Rice and dum', 'அரிசி, டம்', 'Add water and lemon, boil, add rice and cook until water is nearly absorbed. '.$dumEn, 'தண்ணீர், எலுமிச்சை சேர்த்து கொதிக்க வைத்து அரிசி சேர்த்து தண்ணீர் சுண்டும் வரை வேக விடவும். '.$dumTa, 35),
    ], $meatSafety.' Keep the green paste bright: do not brown the onions.', $meatSafetyTa.' பச்சை நிறம் மாறாமல் இருக்க வெங்காயத்தை கருக்க வேண்டாம்.'),

    $r('Mutton Dum Biryani', 'மட்டன் டம் பிரியாணி', 'Main Course', 'Tamil Nadu', 'non_veg', 'Basmati dum biryani with slow-cooked mutton.', 120, 150, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Mutton', 15, 'kg', 'with bone, washed'), $i('Onion', 5, 'kg', 'sliced'), $i('Tomato', 3, 'kg'),
        $i('Curd', 3, 'kg'), $i('Ginger Garlic Paste', 1.2, 'kg'), $i('Green Chilli', 300, 'gram'), $i('Mint Leaves', 5, 'bundle'), $i('Coriander Leaves', 5, 'bundle'),
        $i('Red Chilli Powder', 200, 'gram'), $i('Turmeric Powder', 40, 'gram'), $i('Biryani Masala', 300, 'gram'), $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 1, 'kg'),
        $i('Lemon', 15, 'piece', 'juiced', true), $i('Salt', 500, 'gram'), $i('Water', 18, 'litre', 'includes mutton stock', true),
    ], $spices()), [
        $s('Marinate', 'ஊற வைத்தல்', 'Marinate mutton with curd, half the ginger-garlic paste, chilli powder, turmeric and salt for 2 hours in the chiller.', 'மட்டனை தயிர், பாதி இஞ்சி பூண்டு விழுது, மிளகாய் தூள், மஞ்சள், உப்புடன் குளிர்சாதனத்தில் 2 மணி நேரம் ஊற வைக்கவும்.', 120),
        $s('Cook mutton', 'மட்டன் வேக வைத்தல்', 'Fry whole spices and onion until deep golden, add remaining ginger-garlic paste, tomato and biryani masala until oil separates. Add mutton and pressure-cook (or cook covered) until 90% tender — about 6 whistles or 50 minutes. Keep the stock.', 'முழு மசாலா, வெங்காயம் நன்றாக பொன்னிறமாக வதக்கி, மீதி இஞ்சி பூண்டு விழுது, தக்காளி, பிரியாணி மசாலா எண்ணெய் பிரியும் வரை வதக்கவும். மட்டன் சேர்த்து குக்கரில் (அல்லது மூடி) 90% வேக விடவும் — சுமார் 6 விசில் அல்லது 50 நிமிடம். சாற்றை வைத்துக்கொள்ளவும்.', 70),
        $s('Rice', 'அரிசி', 'Measure stock and add water to 15 L for 10 kg rice, add herbs and lemon, bring to a boil, check salt, add rice and cook on high until most water is absorbed.', '10 கிலோ அரிசிக்கு சாறும் தண்ணீரும் சேர்த்து 15 லிட்டர் ஆக்கி, புதினா கொத்தமல்லி, எலுமிச்சை சேர்த்து கொதிக்க வைத்து உப்பு பார்த்து அரிசி சேர்த்து அதிக தீயில் தண்ணீர் சுண்டும் வரை வேக விடவும்.', 15),
        $s('Dum', 'டம்', $dumEn, $dumTa, 30),
    ], $meatSafety.' Mutton tenderness varies by animal: test pieces before adding rice.', $meatSafetyTa.' ஆட்டுக்கு ஆடு மென்மை மாறும்: அரிசி சேர்க்கும் முன் துண்டுகளை சோதிக்கவும்.'),

    $r('Mutton Seeraga Samba Biryani', 'மட்டன் சீரக சம்பா பிரியாணி', 'Main Course', 'Tamil Nadu', 'non_veg', 'Dindigul-style mutton biryani with seeraga samba rice.', 120, 150, array_merge([
        $i('Seeraga Samba Rice', 10, 'kg', 'soaked 20 minutes'), $i('Mutton', 15, 'kg', 'small pieces with bone'), $i('Small Onion', 4, 'kg', 'peeled'), $i('Tomato', 2.5, 'kg'),
        $i('Curd', 2, 'kg'), $i('Ginger Garlic Paste', 1.2, 'kg'), $i('Green Chilli', 300, 'gram'), $i('Mint Leaves', 5, 'bundle'), $i('Coriander Leaves', 5, 'bundle'),
        $i('Red Chilli Powder', 150, 'gram'), $i('Black Pepper', 60, 'gram', 'crushed'), $i('Fennel Seeds', 60, 'gram'), $i('Turmeric Powder', 30, 'gram'),
        $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 1, 'kg'), $i('Salt', 500, 'gram'), $i('Water', 16, 'litre', 'includes mutton stock', true),
    ], $spices()), [
        $s('Marinate', 'ஊற வைத்தல்', 'Marinate mutton with curd, half the ginger-garlic paste, chilli powder, turmeric and salt for 2 hours in the chiller.', 'மட்டனை தயிர், பாதி இஞ்சி பூண்டு விழுது, மிளகாய் தூள், மஞ்சள், உப்புடன் குளிர்சாதனத்தில் 2 மணி நேரம் ஊற வைக்கவும்.', 120),
        $s('Cook mutton', 'மட்டன் வேக வைத்தல்', 'Fry whole spices, fennel and small onion until golden, add ginger-garlic paste, green chilli, tomato and pepper, then the mutton; pressure-cook until 90% tender. Keep the stock.', 'முழு மசாலா, சோம்பு, சின்ன வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, பச்சை மிளகாய், தக்காளி, மிளகு, பின் மட்டன் சேர்த்து குக்கரில் 90% வேக விடவும். சாற்றை வைத்துக்கொள்ளவும்.', 70),
        $s('Rice', 'அரிசி', 'Make stock plus water up to 15–16 L, add herbs, boil, check salt and add seeraga samba rice; cook on high until nearly absorbed (check after 8 minutes).', 'சாறும் தண்ணீரும் 15–16 லிட்டர் ஆக்கி, புதினா கொத்தமல்லி சேர்த்து கொதிக்க வைத்து உப்பு பார்த்து சீரக சம்பா அரிசி சேர்த்து அதிக தீயில் சுண்டும் வரை வேக விடவும் (8 நிமிடத்தில் பார்க்கவும்).', 15),
        $s('Dum', 'டம்', $dumEn, $dumTa, 30),
    ], $meatSafety, $meatSafetyTa),

    $r('Egg Biryani', 'முட்டை பிரியாணி', 'Main Course', 'Tamil Nadu', 'egg', 'Biryani with roasted boiled eggs, one egg per person.', 60, 70, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Egg', 100, 'piece', 'boiled 10 minutes, peeled'), $i('Onion', 4, 'kg', 'sliced'), $i('Tomato', 2.5, 'kg'),
        $i('Curd', 2, 'kg'), $i('Ginger Garlic Paste', 600, 'gram'), $i('Green Chilli', 250, 'gram'), $i('Mint Leaves', 4, 'bundle'), $i('Coriander Leaves', 4, 'bundle'),
        $i('Red Chilli Powder', 180, 'gram'), $i('Turmeric Powder', 40, 'gram'), $i('Biryani Masala', 250, 'gram'), $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 0.6, 'kg'),
        $i('Salt', 400, 'gram'), $i('Water', 15, 'litre'),
    ], $spices()), [
        $s('Roast eggs', 'முட்டை வறுத்தல்', 'Make shallow slits on the boiled eggs, toss with a little chilli powder, turmeric and salt, and shallow-fry 3–4 minutes until the skin blisters.', 'வேக வைத்த முட்டையில் லேசாக கீறி, சிறிது மிளகாய் தூள், மஞ்சள், உப்பு தடவி 3–4 நிமிடம் தோல் சுருங்கும் வரை லேசாக வறுக்கவும்.', 15),
        $s('Masala', 'மசாலா', 'Fry whole spices and onion until golden, add ginger-garlic paste, green chilli, tomato, chilli powder and biryani masala until oil separates; stir in curd and half the herbs.', 'முழு மசாலா, வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, பச்சை மிளகாய், தக்காளி, மிளகாய் தூள், பிரியாணி மசாலா எண்ணெய் பிரியும் வரை வதக்கி, தயிர், பாதி புதினா கொத்தமல்லி சேர்க்கவும்.', 25),
        $s('Rice', 'அரிசி', 'Add water, boil, make slightly salty, add rice and cook on high until most water is absorbed; press the roasted eggs into the rice.', 'தண்ணீர் சேர்த்து கொதிக்க வைத்து சற்று அதிக உப்பாக்கி, அரிசி சேர்த்து தண்ணீர் சுண்டும் வரை வேக விட்டு, வறுத்த முட்டைகளை சாதத்துக்குள் அழுத்தி வைக்கவும்.', 15),
        $s('Dum', 'டம்', $dumEn, $dumTa, 25),
    ], 'Boil eggs from cold water for 10 minutes and cool in water so they peel cleanly. Count eggs per vessel so every portion gets one. For 250+ cook in separate vessels.', 'முட்டையை குளிர்ந்த தண்ணீரில் இருந்து 10 நிமிடம் வேக வைத்து தண்ணீரில் ஆற வைக்கவும் — தோல் எளிதாக வரும். ஒவ்வொருவருக்கும் ஒன்று கிடைக்கும்படி பாத்திரத்துக்கு எண்ணி வைக்கவும். 250 பேருக்கு மேல் தனி பாத்திரங்கள்.'),

    $r('Mushroom Biryani', 'காளான் பிரியாணி', 'Main Course', 'South Indian', 'veg', 'Basmati biryani with button mushrooms.', 45, 60, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Mushroom', 8, 'kg', 'cleaned, halved'), $i('Onion', 4, 'kg', 'sliced'), $i('Tomato', 2.5, 'kg'),
        $i('Curd', 2, 'kg'), $i('Ginger Garlic Paste', 600, 'gram'), $i('Green Chilli', 250, 'gram'), $i('Mint Leaves', 4, 'bundle'), $i('Coriander Leaves', 4, 'bundle'),
        $i('Red Chilli Powder', 150, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Biryani Masala', 250, 'gram'), $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 0.5, 'kg'),
        $i('Salt', 380, 'gram'), $i('Water', 14, 'litre', 'mushrooms release water — use less', true),
    ], $spices()), [
        $s('Clean mushrooms', 'காளான் சுத்தம்', 'Rinse mushrooms quickly in cold water just before use, drain well and halve; do not soak them.', 'காளானை உபயோகிக்கும் சற்று முன் குளிர்ந்த தண்ணீரில் விரைவாக அலசி, நன்றாக வடித்து இரண்டாக நறுக்கவும்; ஊற வைக்க வேண்டாம்.', 20),
        $s('Masala and mushroom', 'மசாலா, காளான்', 'Fry whole spices and onion until golden, add ginger-garlic paste, green chilli, tomato and powders until oil separates. Add mushrooms and cook on high 6–8 minutes until their water dries up, then curd and half the herbs.', 'முழு மசாலா, வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, பச்சை மிளகாய், தக்காளி, தூள்கள் எண்ணெய் பிரியும் வரை வதக்கவும். காளான் சேர்த்து அதிக தீயில் 6–8 நிமிடம் நீர் சுண்டும் வரை, பின் தயிர், பாதி புதினா கொத்தமல்லி சேர்க்கவும்.', 25),
        $s('Rice', 'அரிசி', 'Add water (slightly less than usual), boil, make slightly salty, add rice and cook on high until most water is absorbed.', 'வழக்கத்தை விட சற்று குறைவான தண்ணீர் சேர்த்து கொதிக்க வைத்து சற்று அதிக உப்பாக்கி, அரிசி சேர்த்து தண்ணீர் சுண்டும் வரை வேக விடவும்.', 15),
        $s('Dum', 'டம்', $dumEn, $dumTa, 25),
    ], 'Mushrooms spoil quickly: buy on the day, keep chilled, and use firm white ones only. For 250+ cook in separate vessels.', 'காளான் சீக்கிரம் கெடும்: அன்றே வாங்கி குளிர்ச்சியாக வைக்கவும், உறுதியான வெள்ளை காளான் மட்டும். 250 பேருக்கு மேல் தனி பாத்திரங்கள்.'),

    $r('Paneer Biryani', 'பனீர் பிரியாணி', 'Main Course', 'North Indian', 'veg', 'Basmati biryani with lightly fried paneer cubes.', 45, 60, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Paneer', 6, 'kg', 'cubed'), $i('Onion', 4, 'kg', 'sliced'), $i('Tomato', 2.5, 'kg'), $i('Curd', 2, 'kg'),
        $i('Ginger Garlic Paste', 600, 'gram'), $i('Green Chilli', 250, 'gram'), $i('Mint Leaves', 4, 'bundle'), $i('Coriander Leaves', 4, 'bundle'),
        $i('Kashmiri Chilli Powder', 150, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Biryani Masala', 250, 'gram'), $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 0.6, 'kg'),
        $i('Salt', 380, 'gram'), $i('Water', 15, 'litre'),
    ], $spices()), [
        $s('Paneer', 'பனீர்', 'Toss paneer with a little chilli, turmeric and salt and shallow-fry 2–3 minutes until light golden; drop into warm salted water so it stays soft.', 'பனீரை சிறிது மிளகாய் தூள், மஞ்சள், உப்புடன் பிசறி 2–3 நிமிடம் லேசான பொன்னிறமாக வறுத்து, மென்மையாக இருக்க வெதுவெதுப்பான உப்புத் தண்ணீரில் போடவும்.', 15),
        $s('Masala', 'மசாலா', 'Fry whole spices and onion until golden, add ginger-garlic paste, green chilli, tomato and powders until oil separates; add curd, half the herbs and the drained paneer.', 'முழு மசாலா, வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, பச்சை மிளகாய், தக்காளி, தூள்கள் எண்ணெய் பிரியும் வரை வதக்கி, தயிர், பாதி புதினா கொத்தமல்லி, வடித்த பனீர் சேர்க்கவும்.', 25),
        $s('Rice', 'அரிசி', 'Add water, boil, make slightly salty, add rice and cook on high until most water is absorbed. Fold gently so the paneer does not break.', 'தண்ணீர் சேர்த்து கொதிக்க வைத்து சற்று அதிக உப்பாக்கி அரிசி சேர்த்து தண்ணீர் சுண்டும் வரை வேக விடவும். பனீர் உடையாமல் மெதுவாக கலக்கவும்.', 15),
        $s('Dum', 'டம்', $dumEn, $dumTa, 25),
    ], 'Keep paneer chilled until frying. For 250+ cook in separate vessels; add paneer at the masala stage only, never at dum, or it turns rubbery.', 'வறுக்கும் வரை பனீரை குளிர்ச்சியாக வைக்கவும். 250 பேருக்கு மேல் தனி பாத்திரங்கள்; பனீரை மசாலா நிலையில் மட்டும் சேர்க்கவும் — டம்மில் சேர்த்தால் ரப்பர் போல ஆகும்.'),

    $r('Baby Corn Biryani', 'பேபி கார்ன் பிரியாணி', 'Main Course', 'South Indian', 'veg', 'Basmati biryani with baby corn and peas.', 45, 60, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Baby Corn', 5, 'kg', 'cut in 1-inch pieces'), $i('Green Peas', 1.5, 'kg'), $i('Onion', 4, 'kg', 'sliced'),
        $i('Tomato', 2.5, 'kg'), $i('Curd', 2, 'kg'), $i('Ginger Garlic Paste', 600, 'gram'), $i('Green Chilli', 250, 'gram'), $i('Mint Leaves', 4, 'bundle'),
        $i('Coriander Leaves', 4, 'bundle'), $i('Red Chilli Powder', 150, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Biryani Masala', 250, 'gram'),
        $i('Sunflower Oil', 1.5, 'litre'), $i('Ghee', 0.5, 'kg'), $i('Salt', 380, 'gram'), $i('Water', 15, 'litre'),
    ], $spices()), [
        $s('Blanch baby corn', 'பேபி கார்ன்', 'Boil baby corn in salted water 4 minutes and drain; it should stay crunchy.', 'பேபி கார்னை உப்புத் தண்ணீரில் 4 நிமிடம் வேக வைத்து வடிக்கவும்; மொறுமொறுப்பு இருக்க வேண்டும்.', 10),
        $s('Masala', 'மசாலா', 'Fry whole spices and onion until golden, add ginger-garlic paste, green chilli, tomato and powders until oil separates; add baby corn, peas, curd and half the herbs and cook 5 minutes.', 'முழு மசாலா, வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, பச்சை மிளகாய், தக்காளி, தூள்கள் எண்ணெய் பிரியும் வரை வதக்கி, பேபி கார்ன், பட்டாணி, தயிர், பாதி புதினா கொத்தமல்லி சேர்த்து 5 நிமிடம் சமைக்கவும்.', 25),
        $s('Rice', 'அரிசி', 'Add water, boil, make slightly salty, add rice and cook on high until most water is absorbed.', 'தண்ணீர் சேர்த்து கொதிக்க வைத்து சற்று அதிக உப்பாக்கி அரிசி சேர்த்து தண்ணீர் சுண்டும் வரை வேக விடவும்.', 15),
        $s('Dum', 'டம்', $dumEn, $dumTa, 25),
    ], 'For 250+ cook in separate vessels of 10 kg rice; the dum time stays 20–25 minutes per vessel.', '250 பேருக்கு மேல் 10 கிலோ அரிசி அளவில் தனி பாத்திரங்கள்; ஒவ்வொன்றுக்கும் டம் நேரம் 20–25 நிமிடம்தான்.'),

    $r('Biryani Rice', 'குஸ்கா (பிரியாணி சாதம்)', 'Main Course', 'Tamil Nadu', 'veg', 'Plain biryani-flavoured rice (kuska) served with gravies.', 30, 50, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Onion', 3, 'kg', 'sliced'), $i('Tomato', 1.5, 'kg'), $i('Ginger Garlic Paste', 500, 'gram'),
        $i('Green Chilli', 200, 'gram'), $i('Mint Leaves', 4, 'bundle'), $i('Coriander Leaves', 4, 'bundle'), $i('Curd', 1, 'kg'), $i('Biryani Masala', 150, 'gram'),
        $i('Red Chilli Powder', 80, 'gram'), $i('Sunflower Oil', 1, 'litre'), $i('Ghee', 0.5, 'kg'), $i('Salt', 350, 'gram'), $i('Water', 15, 'litre'),
    ], $spices()), [
        $s('Masala', 'மசாலா', 'Fry whole spices and onion until golden, add ginger-garlic paste, green chilli, tomato, chilli powder and biryani masala until oil separates; add curd and half the herbs.', 'முழு மசாலா, வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, பச்சை மிளகாய், தக்காளி, மிளகாய் தூள், பிரியாணி மசாலா எண்ணெய் பிரியும் வரை வதக்கி, தயிர், பாதி புதினா கொத்தமல்லி சேர்க்கவும்.', 20),
        $s('Rice', 'அரிசி', 'Add water, boil and make slightly salty; add rice and cook on high until most water is absorbed.', 'தண்ணீர் சேர்த்து கொதிக்க வைத்து சற்று அதிக உப்பாக்கி, அரிசி சேர்த்து தண்ணீர் சுண்டும் வரை வேக விடவும்.', 15),
        $s('Dum', 'டம்', $dumEn, $dumTa, 25),
    ], 'Also the base for combination meals (kuska with chicken gravy or kurma). For 250+ cook in separate vessels.', 'காம்போ சாப்பாட்டுக்கும் இதுவே அடிப்படை (குஸ்கா + சிக்கன் கிரேவி அல்லது குருமா). 250 பேருக்கு மேல் தனி பாத்திரங்கள்.'),

    // ── rice dishes ─────────────────────────────────────────────────────
    $r('Jeera Rice', 'சீரக சாதம்', 'Main Course', 'North Indian', 'veg', 'Basmati rice tempered with cumin and ghee.', 30, 30, [
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Ghee', 0.6, 'kg'), $i('Cumin Seeds', 200, 'gram'), $i('Bay Leaf', 15, 'gram'), $i('Green Chilli', 100, 'gram', 'slit'),
        $i('Salt', 300, 'gram'), $i('Water', 15, 'litre'), $i('Coriander Leaves', 2, 'bundle', 'chopped'),
    ], [
        $s('Temper', 'தாளித்தல்', 'Heat ghee on medium, add cumin and bay leaf and let the cumin crackle and darken one shade (about 30 seconds); add green chilli.', 'நெய்யை மிதமான தீயில் சூடாக்கி சீரகம், பிரியாணி இலை போட்டு சீரகம் வெடித்து சற்று நிறம் மாறும் வரை (சுமார் 30 வினாடி) வறுத்து பச்சை மிளகாய் சேர்க்கவும்.', 3),
        $s('Cook', 'வேக வைத்தல்', 'Add water and salt, bring to a boil, add drained rice and cook on high until the water is absorbed, then cover and cook on the lowest heat 10 minutes.', 'தண்ணீர், உப்பு சேர்த்து கொதிக்க வைத்து வடித்த அரிசி சேர்த்து அதிக தீயில் தண்ணீர் சுண்டும் வரை, பின் மூடி மிகக் குறைந்த தீயில் 10 நிமிடம் வேக விடவும்.', 25),
        $s('Fluff', 'உதிர்த்தல்', 'Rest 5 minutes, fluff with a fork or flat ladle, sprinkle coriander and hold above 65 °C.', '5 நிமிடம் கழித்து தட்டையான கரண்டியால் உதிர்த்து, கொத்தமல்லி தூவி 65 °C க்கு மேல் வைக்கவும்.', 5),
    ], 'Rice to water 1 : 1.5 by volume for soaked basmati. For 250+ cook in vessels of 10 kg rice.', 'ஊறிய பாஸ்மதிக்கு அரிசி : தண்ணீர் = 1 : 1.5 அளவு. 250 பேருக்கு மேல் 10 கிலோ அரிசி அளவில் பாத்திரங்கள்.'),

    $r('Coconut Rice', 'தேங்காய் சாதம்', 'Main Course', 'Tamil Nadu', 'veg', 'Rice tossed with fresh coconut and a cashew tempering.', 30, 40, [
        $i('Raw Rice', 10, 'kg', 'cooked grainy and cooled'), $i('Grated Coconut', 4, 'kg'), $i('Coconut Oil', 0.8, 'litre'), $i('Mustard Seeds', 50, 'gram'),
        $i('Urad Dal', 200, 'gram'), $i('Chana Dal', 200, 'gram'), $i('Cashew Nuts', 400, 'gram'), $i('Dry Red Chilli', 80, 'gram'), $i('Green Chilli', 150, 'gram'),
        $i('Ginger', 100, 'gram', 'chopped'), $i('Curry Leaves', 2, 'bundle'), $i('Asafoetida', 15, 'gram'), $i('Salt', 250, 'gram'),
    ], [
        $s('Cook rice', 'சாதம் வடித்தல்', 'Cook rice grainy, spread on trays and cool 15 minutes.', 'சாதத்தை உதிரியாக வடித்து தட்டுகளில் பரப்பி 15 நிமிடம் ஆற விடவும்.', 35),
        $s('Temper', 'தாளித்தல்', 'Heat coconut oil, splutter mustard, fry dals and cashews golden, add chillies, ginger, curry leaves and asafoetida, then the coconut; stir on low 4–5 minutes until it smells toasty but stays white.', 'தேங்காய் எண்ணெயில் கடுகு வெடித்ததும் பருப்புகள், முந்திரி பொன்னிறமாக வறுத்து, மிளகாய், இஞ்சி, கறிவேப்பிலை, பெருங்காயம், பின் தேங்காய் துருவல் சேர்த்து குறைந்த தீயில் 4–5 நிமிடம் வாசனை வரும் வரை — நிறம் வெள்ளையாகவே இருக்க வேண்டும்.', 10),
        $s('Mix', 'கலத்தல்', 'Add salt, fold into the rice gently and hold covered; serve within 4 hours.', 'உப்பு சேர்த்து சாதத்தில் மெதுவாக கலந்து மூடி வைக்கவும்; 4 மணி நேரத்துக்குள் பரிமாறவும்.', 10),
    ], 'Fresh coconut spoils in heat — do not hold coconut rice in a hot hall beyond 4 hours.', 'புதிய தேங்காய் வெப்பத்தில் கெடும் — தேங்காய் சாதத்தை சூடான அரங்கில் 4 மணி நேரத்துக்கு மேல் வைக்க வேண்டாம்.'),

    $r('Tomato Rice', 'தக்காளி சாதம்', 'Main Course', 'Tamil Nadu', 'veg', 'Spiced tomato rice for lunch boxes and buffets.', 30, 45, [
        $i('Raw Rice', 10, 'kg', 'cooked grainy and cooled'), $i('Tomato', 8, 'kg', 'chopped'), $i('Onion', 3, 'kg', 'sliced'), $i('Ginger Garlic Paste', 400, 'gram'),
        $i('Green Chilli', 200, 'gram'), $i('Red Chilli Powder', 150, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Garam Masala', 60, 'gram'), $i('Sunflower Oil', 1, 'litre'),
        $i('Mustard Seeds', 40, 'gram'), $i('Fennel Seeds', 40, 'gram'), $i('Curry Leaves', 2, 'bundle'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 300, 'gram'),
    ], [
        $s('Cook rice', 'சாதம்', 'Cook rice grainy and cool on trays.', 'சாதத்தை உதிரியாக வடித்து தட்டுகளில் ஆற விடவும்.', 35),
        $s('Tomato masala', 'தக்காளி மசாலா', 'Splutter mustard and fennel in oil, add curry leaves, onion and green chilli until golden, then ginger-garlic paste, tomato, powders and salt; cook 20–25 minutes on medium until thick and oil separates.', 'எண்ணெயில் கடுகு, சோம்பு வெடித்ததும் கறிவேப்பிலை, வெங்காயம், பச்சை மிளகாய் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, தக்காளி, தூள்கள், உப்பு சேர்த்து மிதமான தீயில் 20–25 நிமிடம் கெட்டியாகி எண்ணெய் பிரியும் வரை வதக்கவும்.', 30),
        $s('Mix', 'கலத்தல்', 'Fold the masala into the rice in batches, sprinkle coriander and hold above 65 °C or at room temperature up to 3 hours.', 'மசாலாவை சாதத்தில் பகுதி பகுதியாக கலந்து கொத்தமல்லி தூவி 65 °C க்கு மேல் அல்லது அறை வெப்பத்தில் 3 மணி நேரம் வரை வைக்கவும்.', 10),
    ], 'Cook the tomato masala until oil separates — under-cooked masala makes the rice sour and wet.', 'எண்ணெய் பிரியும் வரை தக்காளி மசாலாவை வதக்கவும் — குறைவாக வதங்கினால் சாதம் புளிப்பாகவும் ஈரமாகவும் இருக்கும்.'),

    $r('Vegetable Pulao', 'வெஜிடபிள் புலாவ்', 'Main Course', 'North Indian', 'veg', 'Mild basmati pulao with mixed vegetables.', 40, 40, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Carrot', 2, 'kg', 'diced'), $i('Beans', 2, 'kg', 'cut'), $i('Green Peas', 1.5, 'kg'), $i('Onion', 2.5, 'kg', 'sliced'),
        $i('Ginger Garlic Paste', 400, 'gram'), $i('Green Chilli', 200, 'gram', 'slit'), $i('Mint Leaves', 2, 'bundle'), $i('Ghee', 0.5, 'kg'), $i('Sunflower Oil', 0.6, 'litre'),
        $i('Salt', 330, 'gram'), $i('Water', 15, 'litre'),
    ], $spices()), [
        $s('Base', 'அடிப்படை', 'Fry whole spices in oil and ghee, add onion and green chilli until soft and light golden, then ginger-garlic paste until the raw smell goes.', 'எண்ணெய், நெய்யில் முழு மசாலா வறுத்து, வெங்காயம், பச்சை மிளகாய் மென்மையாக லேசான பொன்னிறமாக, பின் இஞ்சி பூண்டு விழுது பச்சை வாசனை போகும் வரை வதக்கவும்.', 15),
        $s('Vegetables and rice', 'காய், அரிசி', 'Add vegetables and mint for 3 minutes, then water and salt; boil, add rice and cook on high until the water is absorbed.', 'காய்கறிகள், புதினா 3 நிமிடம், பின் தண்ணீர், உப்பு சேர்த்து கொதிக்க வைத்து அரிசி சேர்த்து தண்ணீர் சுண்டும் வரை வேக விடவும்.', 15),
        $s('Rest', 'ஓய்வு', 'Cover and cook on the lowest heat 10 minutes, rest 5 minutes and fluff gently. Hold above 65 °C.', 'மூடி மிகக் குறைந்த தீயில் 10 நிமிடம், 5 நிமிடம் கழித்து மெதுவாக உதிர்க்கவும். 65 °C க்கு மேல் வைக்கவும்.', 15),
    ], 'For 250+ cook in separate vessels of 10 kg rice. Pulao is milder than biryani — no red chilli or biryani masala.', '250 பேருக்கு மேல் 10 கிலோ அரிசி அளவில் தனி பாத்திரங்கள். புலாவ் பிரியாணியை விட மிதமானது — மிளகாய் தூள், பிரியாணி மசாலா வேண்டாம்.'),

    $r('Peas Pulao', 'பட்டாணி புலாவ்', 'Main Course', 'North Indian', 'veg', 'Basmati pulao with green peas.', 30, 40, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Green Peas', 3, 'kg'), $i('Onion', 2.5, 'kg', 'sliced'), $i('Ginger Garlic Paste', 300, 'gram'),
        $i('Green Chilli', 150, 'gram', 'slit'), $i('Ghee', 0.6, 'kg'), $i('Cumin Seeds', 80, 'gram'), $i('Salt', 320, 'gram'), $i('Water', 15, 'litre'),
    ], $spices()), [
        $s('Base', 'அடிப்படை', 'Heat ghee, fry cumin and whole spices, add onion and green chilli until light golden, then ginger-garlic paste and peas for 3 minutes.', 'நெய்யில் சீரகம், முழு மசாலா வறுத்து, வெங்காயம், பச்சை மிளகாய் லேசான பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, பட்டாணி 3 நிமிடம் வதக்கவும்.', 15),
        $s('Rice', 'அரிசி', 'Add water and salt, boil, add rice and cook on high until absorbed; cover and cook on the lowest heat 10 minutes.', 'தண்ணீர், உப்பு சேர்த்து கொதிக்க வைத்து அரிசி சேர்த்து சுண்டும் வரை, பின் மூடி மிகக் குறைந்த தீயில் 10 நிமிடம்.', 25),
        $s('Fluff', 'உதிர்த்தல்', 'Rest 5 minutes, fluff gently and hold above 65 °C.', '5 நிமிடம் கழித்து மெதுவாக உதிர்த்து 65 °C க்கு மேல் வைக்கவும்.', 5),
    ], 'Frozen peas can go straight in; fresh peas need 5 extra minutes in the base.', 'உறைந்த பட்டாணியை நேரடியாக சேர்க்கலாம்; புதிய பட்டாணிக்கு அடிப்படையில் 5 நிமிடம் கூடுதல்.'),

    $r('Ghee Rice', 'நெய் சாதம்', 'Main Course', 'Kerala', 'veg', 'Fragrant ghee rice with fried onion and cashew.', 30, 40, array_merge([
        $i('Basmati Rice', 10, 'kg', 'soaked 30 minutes'), $i('Ghee', 1, 'kg'), $i('Onion', 2, 'kg', 'thin sliced'), $i('Cashew Nuts', 400, 'gram'), $i('Raisins', 300, 'gram'),
        $i('Salt', 300, 'gram'), $i('Water', 15, 'litre'),
    ], $spices()), [
        $s('Fry garnish', 'அலங்காரம் வறுத்தல்', 'Fry half the onion, cashews and raisins in ghee until golden; keep aside for garnish.', 'பாதி வெங்காயம், முந்திரி, திராட்சையை நெய்யில் பொன்னிறமாக வறுத்து அலங்காரத்துக்கு எடுத்து வைக்கவும்.', 15),
        $s('Cook rice', 'சாதம்', 'In the same ghee fry whole spices and the remaining onion until soft, add water and salt, boil, add rice and cook on high until absorbed; cover on the lowest heat 10 minutes.', 'அதே நெய்யில் முழு மசாலா, மீதி வெங்காயம் மென்மையாக வதக்கி, தண்ணீர், உப்பு சேர்த்து கொதிக்க வைத்து அரிசி சேர்த்து சுண்டும் வரை, பின் மூடி மிகக் குறைந்த தீயில் 10 நிமிடம்.', 25),
        $s('Garnish', 'அலங்கரித்தல்', 'Fluff gently, top with fried onion, cashew and raisins, hold above 65 °C.', 'மெதுவாக உதிர்த்து பொரித்த வெங்காயம், முந்திரி, திராட்சை தூவி 65 °C க்கு மேல் வைக்கவும்.', 5),
    ], 'Pairs with chicken gravy, kurma or dal. For 250+ cook in separate vessels.', 'சிக்கன் கிரேவி, குருமா அல்லது பருப்புடன் பரிமாறலாம். 250 பேருக்கு மேல் தனி பாத்திரங்கள்.'),

    $r('Sambar Rice', 'சாம்பார் சாதம்', 'Main Course', 'Tamil Nadu', 'veg', 'One-pot rice, dal and vegetables (bisi bele style, Tamil taste).', 45, 60, [
        $i('Raw Rice', 7, 'kg'), $i('Toor Dal', 3, 'kg'), $i('Small Onion', 2, 'kg', 'peeled'), $i('Carrot', 1.5, 'kg', 'diced'), $i('Beans', 1.5, 'kg', 'cut'),
        $i('Drumstick', 1.5, 'kg', 'pieces'), $i('Tomato', 2, 'kg'), $i('Tamarind', 400, 'gram', 'soaked'), $i('Sambar Powder', 500, 'gram'), $i('Turmeric Powder', 30, 'gram'),
        $i('Ghee', 0.8, 'kg'), $i('Mustard Seeds', 40, 'gram'), $i('Curry Leaves', 2, 'bundle'), $i('Asafoetida', 20, 'gram'), $i('Coriander Leaves', 3, 'bundle'),
        $i('Salt', 400, 'gram'), $i('Water', 40, 'litre', null, true),
    ], [
        $s('Cook rice and dal', 'அரிசி, பருப்பு', 'Pressure-cook rice and dal together with turmeric and 25 L water until very soft; mash lightly.', 'அரிசியும் பருப்பும் மஞ்சள், 25 லிட்டர் தண்ணீருடன் சேர்த்து குக்கரில் நன்றாக குழைய வேக வைத்து லேசாக மசிக்கவும்.', 30),
        $s('Vegetables', 'காய்கறிகள்', 'Boil vegetables in tamarind extract with sambar powder, salt and 10 L water until tender.', 'புளிக்கரைசலில் சாம்பார் பொடி, உப்பு, 10 லிட்டர் தண்ணீருடன் காய்களை வேக விடவும்.', 15),
        $s('Combine', 'சேர்த்தல்', 'Stir the vegetables into the rice-dal and simmer 10 minutes on low, adding hot water to keep a soft, flowing texture.', 'காய்களை அரிசி-பருப்பில் சேர்த்து குறைந்த தீயில் 10 நிமிடம் கொதிக்க விடவும்; மென்மையாக ஓடும் பதம் வர சுடு தண்ணீர் சேர்க்கவும்.', 10),
        $s('Temper', 'தாளித்தல்', 'Heat ghee, splutter mustard, add small onion, curry leaves and asafoetida until soft, pour over and add coriander. Hold above 65 °C.', 'நெய்யில் கடுகு வெடித்ததும் சின்ன வெங்காயம், கறிவேப்பிலை, பெருங்காயம் வதக்கி ஊற்றி கொத்தமல்லி சேர்க்கவும். 65 °C க்கு மேல் வைக்கவும்.', 8),
    ], 'Sambar rice thickens a lot while holding — keep hot water at the counter to loosen it.', 'சாம்பார் சாதம் வைத்திருக்கும் போது நிறைய கெட்டியாகும் — தளர்த்த பரிமாறும் இடத்தில் சுடு தண்ணீர் வைக்கவும்.'),
];
