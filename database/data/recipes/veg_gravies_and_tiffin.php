<?php

/*
 | Curated (hand-written, not AI) bilingual recipes — vegetarian gravies, breakfast items and chutneys. 100 servings each.
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
$gravyHold = 'Hold gravies above 65 °C and stir from the bottom every 15 minutes. For 250+ cook in two or more vessels; do not fill a vessel more than two-thirds.';
$gravyHoldTa = 'கிரேவியை 65 °C க்கு மேல் வைத்து 15 நிமிடத்துக்கு ஒரு முறை அடியில் இருந்து கிளறவும். 250 பேருக்கு மேல் இரண்டு அல்லது அதிக பாத்திரங்கள்; பாத்திரத்தை மூன்றில் இரண்டு பங்குக்கு மேல் நிரப்ப வேண்டாம்.';

return [
    $r('Paneer Butter Masala', 'பனீர் பட்டர் மசாலா', 'Main Course', 'North Indian', 'veg', 'Creamy tomato-butter gravy with paneer, about 60 g paneer per person.', 40, 50, [
        $i('Paneer', 6, 'kg', 'cubed'), $i('Tomato', 8, 'kg'), $i('Onion', 2, 'kg'), $i('Cashew Nuts', 600, 'gram', 'soaked'), $i('Butter', 1, 'kg'),
        $i('Fresh Cream', 1, 'litre'), $i('Ginger Garlic Paste', 400, 'gram'), $i('Kashmiri Chilli Powder', 150, 'gram'), $i('Garam Masala', 60, 'gram'),
        $i('Kasuri Methi', 40, 'gram', 'crushed'), $i('Sugar', 150, 'gram'), $i('Sunflower Oil', 0.4, 'litre'), $i('Salt', 220, 'gram'), $i('Water', 6, 'litre', null, true),
    ], [
        $s('Tomato-cashew base', 'தக்காளி முந்திரி அடிப்படை', 'Boil tomato, onion and cashew in 4 L water for 15 minutes, cool slightly and blend very smooth; strain for a silky gravy.', 'தக்காளி, வெங்காயம், முந்திரியை 4 லிட்டர் தண்ணீரில் 15 நிமிடம் வேக வைத்து சற்று ஆறியதும் மிக நைசாக அரைத்து, மென்மையான கிரேவிக்கு வடிகட்டவும்.', 30),
        $s('Cook gravy', 'கிரேவி', 'Heat oil and half the butter, fry ginger-garlic paste until the raw smell goes, add chilli powder, then the puree; cook on medium 15–20 minutes until thick and glossy.', 'எண்ணெய், பாதி வெண்ணெய் சூடாக்கி இஞ்சி பூண்டு விழுது பச்சை வாசனை போகும் வரை வதக்கி, மிளகாய் தூள், பின் அரைத்த விழுது சேர்த்து மிதமான தீயில் 15–20 நிமிடம் கெட்டியாகி பளபளக்கும் வரை கொதிக்க விடவும்.', 20),
        $s('Finish', 'முடித்தல்', 'Add salt, sugar, garam masala, kasuri methi, remaining butter and cream; add paneer and simmer 3–4 minutes only. Hold hot, do not boil hard.', 'உப்பு, சர்க்கரை, கரம் மசாலா, கசூரி மேத்தி, மீதி வெண்ணெய், கிரீம் சேர்த்து, பனீர் போட்டு 3–4 நிமிடம் மட்டும் கொதிக்க விடவும். சூடாக வைக்கவும், பலமாக கொதிக்க வேண்டாம்.', 8),
    ], $gravyHold.' Add paneer near service time; long boiling makes it hard.', $gravyHoldTa.' பரிமாறும் நேரத்துக்கு அருகில் பனீர் சேர்க்கவும்; நீண்ட நேரம் கொதித்தால் கடினமாகும்.'),

    $r('Paneer Kurma', 'பனீர் குருமா', 'Main Course', 'Tamil Nadu', 'veg', 'Coconut-cashew kurma with paneer, for chapati, parotta and pulao.', 40, 45, [
        $i('Paneer', 6, 'kg', 'cubed'), $i('Onion', 2.5, 'kg', 'sliced'), $i('Tomato', 2, 'kg'), $i('Grated Coconut', 2, 'kg'), $i('Cashew Nuts', 400, 'gram', 'soaked'),
        $i('Fennel Seeds', 60, 'gram'), $i('Green Chilli', 250, 'gram'), $i('Ginger Garlic Paste', 400, 'gram'), $i('Turmeric Powder', 20, 'gram'), $i('Garam Masala', 50, 'gram'),
        $i('Sunflower Oil', 0.6, 'litre'), $i('Cinnamon', 20, 'gram'), $i('Cloves', 15, 'gram'), $i('Salt', 230, 'gram'), $i('Water', 8, 'litre'), $i('Coriander Leaves', 2, 'bundle'),
    ], [
        $s('Paste', 'விழுது', 'Grind coconut, cashew, fennel and green chilli to a very smooth paste.', 'தேங்காய், முந்திரி, சோம்பு, பச்சை மிளகாய் மிக நைசாக அரைக்கவும்.', 15),
        $s('Base', 'அடிப்படை', 'Fry cinnamon and cloves in oil, add onion until soft and light golden, then ginger-garlic paste, tomato and turmeric until soft.', 'எண்ணெயில் பட்டை, கிராம்பு வறுத்து, வெங்காயம் மென்மையாக லேசான பொன்னிறமாக, பின் இஞ்சி பூண்டு விழுது, தக்காளி, மஞ்சள் மென்மையாகும் வரை வதக்கவும்.', 15),
        $s('Simmer', 'கொதிக்க விடுதல்', 'Add the paste, water and salt and simmer 10 minutes on low, stirring often; add paneer and garam masala for 3 minutes, finish with coriander.', 'விழுது, தண்ணீர், உப்பு சேர்த்து குறைந்த தீயில் அடிக்கடி கிளறி 10 நிமிடம் கொதிக்க விட்டு, பனீர், கரம் மசாலா சேர்த்து 3 நிமிடம், கொத்தமல்லி தூவவும்.', 15),
    ], $gravyHold, $gravyHoldTa),

    $r('Rajma Masala', 'ராஜ்மா மசாலா', 'Main Course', 'North Indian', 'veg', 'Red kidney bean curry for rice and chapati.', 30, 70, [
        $i('Rajma', 5, 'kg', 'soaked 10–12 hours'), $i('Onion', 4, 'kg', 'finely chopped'), $i('Tomato', 4, 'kg', 'pureed'), $i('Ginger Garlic Paste', 500, 'gram'),
        $i('Red Chilli Powder', 100, 'gram'), $i('Coriander Powder', 150, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Garam Masala', 80, 'gram'),
        $i('Cumin Seeds', 50, 'gram'), $i('Sunflower Oil', 0.8, 'litre'), $i('Butter', 0.3, 'kg', null, false, true), $i('Coriander Leaves', 3, 'bundle'),
        $i('Salt', 250, 'gram'), $i('Water', 18, 'litre'),
    ], [
        $s('Cook rajma', 'ராஜ்மா வேக வைத்தல்', 'Discard the soaking water, pressure-cook rajma with fresh water and salt for 8–10 whistles until a bean mashes easily. Red kidney beans must be fully boiled — undercooked rajma is toxic.', 'ஊறிய தண்ணீரை எறிந்து, புதிய தண்ணீர், உப்புடன் குக்கரில் 8–10 விசில் — ஒரு பீன்ஸ் எளிதாக மசிய வேண்டும். ராஜ்மா முழுமையாக வேக வேண்டும் — குறைவாக வெந்தால் விஷத்தன்மை உண்டு.', 45),
        $s('Masala', 'மசாலா', 'Splutter cumin in oil, fry onion deep golden, add ginger-garlic paste, powders with a splash of water, then tomato puree; cook until oil separates.', 'எண்ணெயில் சீரகம் வெடித்ததும் வெங்காயம் நன்றாக பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, தூள்களை சிறிது தண்ணீருடன், பின் தக்காளி விழுது சேர்த்து எண்ணெய் பிரியும் வரை வதக்கவும்.', 25),
        $s('Simmer', 'கொதிக்க விடுதல்', 'Add rajma with its cooking liquid, simmer 20 minutes, mash a few beans to thicken; finish with garam masala, butter and coriander.', 'ராஜ்மாவை அதன் தண்ணீருடன் சேர்த்து 20 நிமிடம் கொதிக்க விட்டு, சில பீன்ஸை மசித்து கெட்டியாக்கி, கரம் மசாலா, வெண்ணெய், கொத்தமல்லி சேர்க்கவும்.', 20),
    ], $gravyHold.' Never use slow-cooked or half-boiled rajma: boil hard for at least 10 minutes.', $gravyHoldTa.' அரைகுறையாக வெந்த ராஜ்மாவை உபயோகிக்க வேண்டாம்: குறைந்தது 10 நிமிடம் நன்றாக கொதிக்க வேண்டும்.'),

    $r('Dal Tadka', 'தால் தட்கா', 'Main Course', 'North Indian', 'veg', 'Yellow dal with a ghee-cumin-garlic tempering.', 20, 45, [
        $i('Toor Dal', 4, 'kg'), $i('Moong Dal', 1, 'kg'), $i('Onion', 2, 'kg', 'chopped'), $i('Tomato', 2, 'kg', 'chopped'), $i('Garlic', 300, 'gram', 'chopped'),
        $i('Green Chilli', 200, 'gram'), $i('Turmeric Powder', 30, 'gram'), $i('Red Chilli Powder', 60, 'gram'), $i('Cumin Seeds', 60, 'gram'), $i('Ghee', 0.6, 'kg'),
        $i('Dry Red Chilli', 40, 'gram'), $i('Asafoetida', 20, 'gram'), $i('Coriander Leaves', 3, 'bundle'), $i('Salt', 280, 'gram'), $i('Water', 25, 'litre'),
    ], [
        $s('Cook dal', 'பருப்பு வேக வைத்தல்', 'Pressure-cook both dals with turmeric and 15 L water until soft; whisk.', 'இரண்டு பருப்பையும் மஞ்சள், 15 லிட்டர் தண்ணீருடன் குக்கரில் குழைய வேக வைத்து கடையவும்.', 25),
        $s('Masala', 'மசாலா', 'Cook onion, green chilli and tomato in half the ghee until soft, add chilli powder and the dal with the remaining water and salt; simmer 10 minutes.', 'பாதி நெய்யில் வெங்காயம், பச்சை மிளகாய், தக்காளி மென்மையாக வதக்கி, மிளகாய் தூள், பருப்பு, மீதி தண்ணீர், உப்பு சேர்த்து 10 நிமிடம் கொதிக்க விடவும்.', 15),
        $s('Tadka', 'தட்கா', 'Heat the remaining ghee, add cumin, garlic, dry red chilli and asafoetida until the garlic is golden; pour over the dal and add coriander.', 'மீதி நெய்யில் சீரகம், பூண்டு, காய்ந்த மிளகாய், பெருங்காயம் பூண்டு பொன்னிறமாகும் வரை வறுத்து பருப்பில் ஊற்றி கொத்தமல்லி சேர்க்கவும்.', 5),
    ], $gravyHold.' Dal thickens on standing — loosen with hot water.', $gravyHoldTa.' பருப்பு வைத்திருக்கும் போது கெட்டியாகும் — சுடு தண்ணீரால் தளர்த்தவும்.'),

    $r('Mixed Vegetable Curry', 'கலப்பு காய்கறி கறி', 'Side Dish', 'South Indian', 'veg', 'Semi-dry mixed vegetable curry with onion-tomato masala.', 50, 35, [
        $i('Carrot', 2, 'kg', 'diced'), $i('Beans', 2, 'kg', 'cut'), $i('Potato', 3, 'kg', 'cubed'), $i('Cauliflower', 2, 'kg', 'florets'), $i('Green Peas', 1, 'kg'),
        $i('Capsicum', 1, 'kg', 'diced'), $i('Onion', 2, 'kg', 'chopped'), $i('Tomato', 2, 'kg', 'chopped'), $i('Ginger Garlic Paste', 300, 'gram'),
        $i('Red Chilli Powder', 100, 'gram'), $i('Coriander Powder', 100, 'gram'), $i('Turmeric Powder', 25, 'gram'), $i('Garam Masala', 50, 'gram'),
        $i('Sunflower Oil', 0.6, 'litre'), $i('Coriander Leaves', 2, 'bundle'), $i('Salt', 220, 'gram'), $i('Water', 3, 'litre', null, true),
    ], [
        $s('Par-cook', 'பாதி வேக வைத்தல்', 'Boil potato, carrot, beans and cauliflower in salted water until just tender (8–10 minutes); drain.', 'உருளை, கேரட், பீன்ஸ், காலிஃபிளவரை உப்புத் தண்ணீரில் 8–10 நிமிடம் வேக வைத்து வடிக்கவும்.', 15),
        $s('Masala', 'மசாலா', 'Fry onion in oil until golden, add ginger-garlic paste, tomato and powders; cook until oil separates.', 'எண்ணெயில் வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, தக்காளி, தூள்கள் சேர்த்து எண்ணெய் பிரியும் வரை வதக்கவும்.', 15),
        $s('Toss', 'கலத்தல்', 'Add all vegetables, capsicum and a little water, toss on medium 5–6 minutes until coated and semi-dry; finish with garam masala and coriander.', 'எல்லா காய்கள், குடைமிளகாய், சிறிது தண்ணீர் சேர்த்து மிதமான தீயில் 5–6 நிமிடம் மசாலா ஒட்டி பாதி உலர்ந்த பதம் வரும் வரை கிளறி, கரம் மசாலா, கொத்தமல்லி சேர்க்கவும்.', 8),
    ], 'Par-cook vegetables separately by type for big batches so none turns mushy.', 'பெரிய அளவுக்கு காய்களை வகை வாரியாக தனித்தனியாக பாதி வேக வைக்கவும் — எதுவும் குழையாது.'),

    $r('Potato Masala', 'பூரி கிழங்கு', 'Side Dish', 'Tamil Nadu', 'veg', 'Onion-potato masala for poori and dosa.', 40, 35, [
        $i('Potato', 10, 'kg', 'boiled, peeled, roughly mashed'), $i('Onion', 4, 'kg', 'sliced'), $i('Green Chilli', 300, 'gram', 'slit'), $i('Ginger', 200, 'gram', 'chopped'),
        $i('Gram Flour', 150, 'gram', 'mixed in 1 L water'), $i('Turmeric Powder', 30, 'gram'), $i('Sunflower Oil', 0.5, 'litre'), $i('Mustard Seeds', 40, 'gram'),
        $i('Urad Dal', 60, 'gram'), $i('Chana Dal', 60, 'gram'), $i('Curry Leaves', 2, 'bundle'), $i('Coriander Leaves', 2, 'bundle'), $i('Salt', 200, 'gram'),
        $i('Water', 6, 'litre', null, true),
    ], [
        $s('Temper', 'தாளித்தல்', 'Splutter mustard in oil, fry the dals golden, add curry leaves, green chilli, ginger and onion; cook until onion is soft but not brown.', 'எண்ணெயில் கடுகு வெடித்ததும் பருப்புகள் பொன்னிறமாக வறுத்து, கறிவேப்பிலை, பச்சை மிளகாய், இஞ்சி, வெங்காயம் சேர்த்து வெங்காயம் மென்மையாகும் வரை (பழுப்பாகாமல்) வதக்கவும்.', 12),
        $s('Potato', 'கிழங்கு', 'Add turmeric, salt, water and the potatoes; boil 5 minutes.', 'மஞ்சள், உப்பு, தண்ணீர், உருளைக்கிழங்கு சேர்த்து 5 நிமிடம் கொதிக்க விடவும்.', 8),
        $s('Thicken', 'கெட்டியாக்குதல்', 'Stir in the gram-flour water and cook 3–4 minutes until it thickens to a soft, spoonable masala; add coriander.', 'கடலை மாவு கரைசலை சேர்த்து 3–4 நிமிடம் கரண்டியால் எடுக்கும் மென்மையான பதம் வரும் வரை கொதிக்க விட்டு கொத்தமல்லி சேர்க்கவும்.', 5),
    ], 'Keep 1–2 L hot water ready — the masala thickens while holding. Hold above 65 °C.', 'மசாலா கெட்டியாகும் — 1–2 லிட்டர் சுடு தண்ணீர் தயாராக வைக்கவும். 65 °C க்கு மேல் வைக்கவும்.'),

    $r('Aloo Gobi', 'ஆலு கோபி', 'Side Dish', 'North Indian', 'veg', 'Dry potato and cauliflower stir-fry.', 40, 35, [
        $i('Potato', 6, 'kg', 'cubed'), $i('Cauliflower', 6, 'kg', 'florets, soaked in warm salt water 10 minutes'), $i('Onion', 2, 'kg', 'chopped'), $i('Tomato', 1.5, 'kg'),
        $i('Ginger Garlic Paste', 250, 'gram'), $i('Green Chilli', 150, 'gram'), $i('Red Chilli Powder', 80, 'gram'), $i('Coriander Powder', 100, 'gram'),
        $i('Turmeric Powder', 25, 'gram'), $i('Garam Masala', 50, 'gram'), $i('Cumin Seeds', 50, 'gram'), $i('Sunflower Oil', 0.8, 'litre'), $i('Coriander Leaves', 2, 'bundle'),
        $i('Salt', 200, 'gram'),
    ], [
        $s('Clean cauliflower', 'காலிஃபிளவர் சுத்தம்', 'Soak florets in warm salt water 10 minutes to remove insects, rinse and drain well.', 'பூச்சிகள் போக காலிஃபிளவரை வெதுவெதுப்பான உப்புத் தண்ணீரில் 10 நிமிடம் ஊற வைத்து அலசி நன்றாக வடிக்கவும்.', 15),
        $s('Masala', 'மசாலா', 'Splutter cumin in oil, add onion until golden, then ginger-garlic paste, green chilli, tomato and powders until oil separates.', 'எண்ணெயில் சீரகம் வெடித்ததும் வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, பச்சை மிளகாய், தக்காளி, தூள்கள் எண்ணெய் பிரியும் வரை வதக்கவும்.', 15),
        $s('Cook', 'வேக வைத்தல்', 'Add potato and cauliflower with salt, toss, cover and cook on low 15–18 minutes, stirring gently, until tender but whole; finish with garam masala and coriander.', 'உருளை, காலிஃபிளவர், உப்பு சேர்த்து கிளறி மூடி குறைந்த தீயில் மெதுவாக கிளறி 15–18 நிமிடம் உருவம் குலையாமல் வேக விட்டு கரம் மசாலா, கொத்தமல்லி சேர்க்கவும்.', 18),
    ], 'Cook in wide pans in batches of up to 12 kg vegetables so it stays dry.', 'உலர்ந்த பதத்துக்கு அகலமான வாணலியில் 12 கிலோ காய் வரை ஒரு தடவை.'),

    $r('Mushroom Masala', 'காளான் மசாலா', 'Side Dish', 'South Indian', 'veg', 'Semi-gravy mushroom masala.', 30, 35, [
        $i('Mushroom', 8, 'kg', 'cleaned, quartered'), $i('Onion', 3, 'kg', 'chopped'), $i('Tomato', 3, 'kg', 'chopped'), $i('Capsicum', 1, 'kg', 'diced'),
        $i('Ginger Garlic Paste', 300, 'gram'), $i('Red Chilli Powder', 100, 'gram'), $i('Coriander Powder', 100, 'gram'), $i('Turmeric Powder', 20, 'gram'),
        $i('Garam Masala', 60, 'gram'), $i('Black Pepper', 30, 'gram', 'crushed'), $i('Sunflower Oil', 0.6, 'litre'), $i('Coriander Leaves', 2, 'bundle'), $i('Salt', 200, 'gram'),
    ], [
        $s('Masala', 'மசாலா', 'Fry onion in oil until golden, add ginger-garlic paste, tomato and powders; cook until oil separates.', 'எண்ணெயில் வெங்காயம் பொன்னிறமாக வதக்கி, இஞ்சி பூண்டு விழுது, தக்காளி, தூள்கள் சேர்த்து எண்ணெய் பிரியும் வரை வதக்கவும்.', 20),
        $s('Mushroom', 'காளான்', 'Add mushrooms and salt on high heat; cook 8–10 minutes until the released water reduces to a thick gravy. Add capsicum for the last 3 minutes.', 'காளான், உப்பு சேர்த்து அதிக தீயில் 8–10 நிமிடம் — வெளியேறும் நீர் சுண்டி கெட்டியான கிரேவி ஆகும் வரை. கடைசி 3 நிமிடத்தில் குடைமிளகாய் சேர்க்கவும்.', 12),
        $s('Finish', 'முடித்தல்', 'Add pepper, garam masala and coriander; hold above 65 °C.', 'மிளகு, கரம் மசாலா, கொத்தமல்லி சேர்த்து 65 °C க்கு மேல் வைக்கவும்.', 3),
    ], 'Use mushrooms bought on the day and kept chilled; discard slimy or dark ones.', 'அன்று வாங்கி குளிர்ச்சியாக வைத்த காளான் மட்டும்; வழுவழுப்பான, கருத்த காளானை எறியவும்.'),

    // ── tiffin & chutneys ───────────────────────────────────────────────
    $r('Dosa Batter', 'தோசை மாவு', 'Breakfast', 'Tamil Nadu', 'veg', 'Fermented dosa batter for about 3 dosas per person.', 60, 60, [
        $i('Raw Rice', 5, 'kg', 'soaked 5 hours'), $i('Idli Rice', 2, 'kg', 'soaked 5 hours'), $i('Urad Dal', 1.5, 'kg', 'soaked 4 hours'),
        $i('Fenugreek Seeds', 40, 'gram'), $i('Chana Dal', 100, 'gram', 'for colour, soaked with urad', false, true), $i('Salt', 150, 'gram'),
        $i('Sunflower Oil', 1, 'litre', 'for the tawa', true),
    ], [
        $s('Grind', 'அரைத்தல்', 'Grind urad dal with fenugreek until fluffy (20 minutes), then the rice to a smooth batter. Mix both with salt.', 'உளுந்தையும் வெந்தயத்தையும் 20 நிமிடம் பஞ்சு போல் அரைத்து, அரிசியை நைசாக அரைக்கவும். இரண்டையும் உப்புடன் கலக்கவும்.', 45),
        $s('Ferment', 'புளிக்க விடுதல்', 'Ferment 8–10 hours in clean containers filled only half way; stir and chill once risen.', 'சுத்தமான பாத்திரங்களில் பாதி அளவு மட்டும் நிரப்பி 8–10 மணி நேரம் புளிக்க விடவும்; பொங்கியதும் கலக்கி குளிர்சாதனத்தில் வைக்கவும்.', 540),
        $s('Make dosas', 'தோசை வார்த்தல்', 'Heat the tawa, sprinkle water (it should sizzle off), wipe, pour a ladle of batter and spread thin in circles; drizzle oil and cook on medium until the bottom is golden and crisp.', 'தோசைக்கல்லை சூடாக்கி தண்ணீர் தெளித்து (சீறி ஆவியாக வேண்டும்) துடைத்து, ஒரு கரண்டி மாவு ஊற்றி வட்டமாக மெல்லியதாக பரப்பவும்; எண்ணெய் விட்டு மிதமான தீயில் அடி பொன்னிறமாக மொறுமொறுப்பாகும் வரை சுடவும்.', 60),
    ], 'One dosa master makes about 60 dosas an hour per tawa — plan tawas and staff for the head count. Keep fermented batter chilled; use within 2 days.', 'ஒரு தோசைக்கல்லில் ஒருவர் மணிக்கு சுமார் 60 தோசை — எண்ணிக்கைக்கு ஏற்ப கல்லும் ஆட்களும் திட்டமிடவும். புளித்த மாவை குளிர்ச்சியாக வைத்து 2 நாளுக்குள் உபயோகிக்கவும்.'),

    $r('Tomato Chutney', 'தக்காளி சட்னி', 'Side Dish', 'Tamil Nadu', 'veg', 'Spicy onion-tomato chutney for idli and dosa.', 20, 30, [
        $i('Tomato', 6, 'kg', 'chopped'), $i('Onion', 2, 'kg', 'chopped'), $i('Dry Red Chilli', 150, 'gram'), $i('Garlic', 200, 'gram'), $i('Tamarind', 80, 'gram'),
        $i('Gingelly Oil', 0.5, 'litre'), $i('Mustard Seeds', 30, 'gram'), $i('Urad Dal', 60, 'gram'), $i('Curry Leaves', 1, 'bundle'), $i('Asafoetida', 10, 'gram'),
        $i('Salt', 150, 'gram'),
    ], [
        $s('Cook', 'வதக்குதல்', 'Fry red chillies and garlic in some oil, add onion until soft, then tomato, tamarind and salt; cook 15 minutes until the tomato is pulpy and dry.', 'சிறிது எண்ணெயில் காய்ந்த மிளகாய், பூண்டு வறுத்து, வெங்காயம் மென்மையாக, பின் தக்காளி, புளி, உப்பு சேர்த்து 15 நிமிடம் குழைந்து ஈரம் சுண்டும் வரை வதக்கவும்.', 20),
        $s('Grind', 'அரைத்தல்', 'Cool slightly and grind smooth without water.', 'சற்று ஆறியதும் தண்ணீர் இல்லாமல் நைசாக அரைக்கவும்.', 10),
        $s('Temper', 'தாளித்தல்', 'Splutter mustard and urad dal in the remaining oil with curry leaves and asafoetida; pour over the chutney.', 'மீதி எண்ணெயில் கடுகு, உளுந்து, கறிவேப்பிலை, பெருங்காயம் தாளித்து சட்னியில் ஊற்றவும்.', 5),
    ], 'Keeps better than coconut chutney but still hold below 8 °C and use the same day.', 'தேங்காய் சட்னியை விட நீடிக்கும், ஆனாலும் 8 °C க்கு கீழ் வைத்து அன்றே உபயோகிக்கவும்.'),

    $r('Mint Chutney', 'புதினா சட்னி', 'Side Dish', 'Tamil Nadu', 'veg', 'Mint-coconut chutney for dosa, samosa and biryani.', 30, 10, [
        $i('Mint Leaves', 10, 'bundle', 'leaves only, washed'), $i('Coriander Leaves', 5, 'bundle'), $i('Grated Coconut', 2, 'kg'), $i('Roasted Gram', 400, 'gram'),
        $i('Green Chilli', 200, 'gram'), $i('Ginger', 100, 'gram'), $i('Tamarind', 60, 'gram'), $i('Salt', 100, 'gram'), $i('Water', 2.5, 'litre', 'cold', true),
        $i('Sunflower Oil', 0.1, 'litre'), $i('Mustard Seeds', 20, 'gram'), $i('Urad Dal', 40, 'gram'),
    ], [
        $s('Wash', 'கழுவுதல்', 'Pick mint leaves, wash in three changes of water and drain well.', 'புதினா இலைகளை ஆய்ந்து மூன்று தண்ணீரில் கழுவி நன்றாக வடிக்கவும்.', 15),
        $s('Grind', 'அரைத்தல்', 'Grind mint, coriander, coconut, roasted gram, chilli, ginger, tamarind and salt with cold water to a smooth chutney.', 'புதினா, கொத்தமல்லி, தேங்காய், பொட்டுக்கடலை, மிளகாய், இஞ்சி, புளி, உப்பு குளிர்ந்த தண்ணீருடன் நைசாக அரைக்கவும்.', 15),
        $s('Temper and chill', 'தாளித்து குளிர்வித்தல்', 'Temper mustard and urad dal in oil, pour over, mix and keep below 5 °C; serve within 2 hours.', 'எண்ணெயில் கடுகு, உளுந்து தாளித்து ஊற்றி கலந்து 5 °C க்கு கீழ் வைக்கவும்; 2 மணி நேரத்துக்குள் பரிமாறவும்.', 5),
    ], 'Raw herbs and coconut: wash well, grind close to service and keep chilled.', 'பச்சை இலைகளும் தேங்காயும்: நன்றாக கழுவி, பரிமாறும் நேரத்துக்கு அருகில் அரைத்து குளிர்ச்சியாக வைக்கவும்.'),

    $r('Poori', 'பூரி', 'Breakfast', 'North Indian', 'veg', 'Puffed fried wheat bread, about 3 per person.', 45, 75, [
        $i('Wheat Flour', 7, 'kg'), $i('Rava', 0.5, 'kg', 'for crispness'), $i('Salt', 100, 'gram'), $i('Sunflower Oil', 10, 'litre', 'for frying; about 2.5 L absorbed', true),
        $i('Water', 3.5, 'litre', null, true),
    ], [
        $s('Dough', 'மாவு பிசைதல்', 'Mix atta, rava, salt and 2 tbsp oil per kg; knead to a stiff, smooth dough with little water and rest 20 minutes — soft dough soaks oil.', 'கோதுமை மாவு, ரவை, உப்பு, கிலோவுக்கு 2 மேசைக்கரண்டி எண்ணெய் சேர்த்து குறைந்த தண்ணீருடன் கெட்டியாக மென்மையாக பிசைந்து 20 நிமிடம் வைக்கவும் — தளர்வான மாவு எண்ணெய் குடிக்கும்.', 30),
        $s('Roll', 'தேய்த்தல்', 'Divide into 30 g balls and roll 4-inch rounds with a little oil (no dry flour — it burns in the oil).', '30 கிராம் உருண்டைகளாக்கி சிறிது எண்ணெய் தடவி 4 அங்குல வட்டமாக தேய்க்கவும் (மாவு தூவ வேண்டாம் — எண்ணெயில் கருகும்).', 30),
        $s('Fry', 'பொரித்தல்', 'Heat oil to about 180 °C, slide in one poori, press gently with the slotted spoon until it puffs, turn and fry until light golden. Drain upright.', 'எண்ணெயை சுமார் 180 °C க்கு சூடாக்கி ஒரு பூரி போட்டு ஜல்லிக்கரண்டியால் லேசாக அழுத்தினால் உப்பும்; திருப்பி லேசான பொன்னிறமாக பொரித்து நிமிர்த்தி வடிக்கவும்.', 45),
    ], 'Pooris deflate in 20 minutes: fry in waves matched to serving. Keep the fryer guarded and oil below smoking point.', 'பூரி 20 நிமிடத்தில் சுருங்கும்: பரிமாறலுக்கு ஏற்ப அலை அலையாக பொரிக்கவும். பொரிப்பானை கவனமாக காக்கவும், எண்ணெய் புகைக்கக் கூடாது.'),

    $r('Parotta', 'பரோட்டா', 'Breakfast', 'Tamil Nadu', 'veg', 'Layered maida parotta, about 2 per person.', 180, 90, [
        $i('Maida', 9, 'kg'), $i('Sugar', 150, 'gram'), $i('Salt', 120, 'gram'), $i('Milk', 1, 'litre', null, false, true), $i('Sunflower Oil', 2.5, 'litre', 'for dough, resting and tawa', true),
        $i('Water', 4.5, 'litre', null, true),
    ], [
        $s('Knead', 'பிசைதல்', 'Knead maida with sugar, salt, milk and water for 15 minutes into a very soft, elastic dough; coat with oil and rest 2 hours covered.', 'மைதாவை சர்க்கரை, உப்பு, பால், தண்ணீருடன் 15 நிமிடம் மிக மென்மையாக இழுவைத்தன்மையுடன் பிசைந்து எண்ணெய் தடவி மூடி 2 மணி நேரம் வைக்கவும்.', 135),
        $s('Balls and rest', 'உருண்டை', 'Make 80 g balls, coat in oil and rest another 30 minutes.', '80 கிராம் உருண்டைகளாக்கி எண்ணெயில் புரட்டி மேலும் 30 நிமிடம் வைக்கவும்.', 40),
        $s('Spread and coil', 'வீசி சுருட்டுதல்', 'Spread each ball paper-thin on an oiled counter, gather into a pleated rope, coil and rest 10 minutes.', 'எண்ணெய் தடவிய மேடையில் ஒவ்வொரு உருண்டையையும் பேப்பர் போல் மெல்லியதாக வீசி, மடிப்புக் கயிறாக சேர்த்து சுருட்டி 10 நிமிடம் வைக்கவும்.', 40),
        $s('Cook', 'சுடுதல்', 'Flatten coils to 6-inch rounds, cook on a medium-hot tawa with oil until golden spots appear on both sides, then stack and clap from the sides to open the layers.', 'சுருள்களை 6 அங்குலமாக தட்டி, மிதமான சூட்டில் எண்ணெய் விட்டு இருபுறமும் பொன்னிற புள்ளிகள் வரும் வரை சுட்டு, அடுக்கி பக்கவாட்டில் தட்டி அடுக்குகளை பிரிக்கவும்.', 60),
    ], 'Needs a trained parotta master: one master makes about 50 per hour. For 250+ plan two masters and start dough 4 hours ahead.', 'பயிற்சி பெற்ற பரோட்டா மாஸ்டர் தேவை: ஒருவர் மணிக்கு சுமார் 50. 250 பேருக்கு மேல் இருவர், மாவை 4 மணி நேரம் முன்பே தொடங்கவும்.'),
];
