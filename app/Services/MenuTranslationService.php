<?php

namespace App\Services;

/**
 * Translates menu item names from English to Tamil.
 *
 * Strategy: tiered lookup over a curated food/catering dictionary.
 *   1. Exact phrase match (case-insensitive)
 *   2. Word-by-word translation, joined with spaces
 *   3. null → caller shows no suggestion
 *
 * Future providers (Google Translate, OpenAI, etc.) can be swapped in
 * by replacing or extending the translate() method. The dictionary layer
 * always runs first so the app works offline and avoids API calls for
 * known terms.
 */
class MenuTranslationService
{
    /**
     * Translate an English menu item name to Tamil.
     * Returns null when no suggestion is available.
     */
    public function translate(string $english): ?string
    {
        $english = trim($english);
        if ($english === '') return null;

        // 1. Exact phrase
        $exact = $this->exactMatch($english);
        if ($exact !== null) return $exact;

        // 2. Word-by-word
        return $this->wordByWord($english);
    }

    // ── Lookups ───────────────────────────────────────────────────────────────

    private function exactMatch(string $text): ?string
    {
        $key = mb_strtolower($text);
        return $this->dictionary()[$key] ?? null;
    }

    private function wordByWord(string $text): ?string
    {
        $dict  = $this->dictionary();
        $words = preg_split('/[\s\/\-]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) return null;

        $parts     = [];
        $anyHit    = false;

        foreach ($words as $word) {
            $key = mb_strtolower($word);
            if (isset($dict[$key])) {
                $parts[]  = $dict[$key];
                $anyHit   = true;
            } else {
                // Keep untranslated word as-is (e.g. brand names, numbers)
                $parts[] = $word;
            }
        }

        // Only return a result if at least one word translated
        return $anyHit ? implode(' ', $parts) : null;
    }

    // ── Dictionary ────────────────────────────────────────────────────────────

    /**
     * Food & catering domain dictionary.
     * Keys are lowercase English. Values are Tamil equivalents.
     * Add entries here to improve coverage — no code changes needed elsewhere.
     */
    private function dictionary(): array
    {
        return [
            // ── Cooking methods & descriptors ──────────────────────────────
            'fry'         => 'வறுவல்',
            'fries'       => 'வறுவல்',
            'fried'       => 'வறுத்த',
            'roasted'     => 'வறுத்த',
            'baked'       => 'சுட்ட',
            'steamed'     => 'வேகவைத்த',
            'grilled'     => 'கிரில்டு',
            'tandoor'     => 'தந்தூர்',
            'tandoori'    => 'தந்தூரி',
            'spiced'      => 'மசாலா',
            'spicy'       => 'காரமான',
            'sweet'       => 'இனிப்பு',
            'sour'        => 'புளிப்பு',
            'hot'         => 'சூடான',
            'cold'        => 'குளிரான',
            'fresh'       => 'புதிய',
            'special'     => 'சிறப்பு',
            'premium'     => 'தரமான',
            'homemade'    => 'வீட்டில் செய்த',
            'traditional' => 'பாரம்பரிய',
            'classic'     => 'கிளாசிக்',
            'mini'        => 'சிறிய',
            'large'       => 'பெரிய',
            'mixed'       => 'கலவை',
            'mix'         => 'கலவை',
            'stuffed'     => 'அடைத்த',
            'filled'      => 'நிரப்பிய',
            'plain'       => 'சாதா',
            'masala'      => 'மசாலா',
            'gravy'       => 'குழம்பு',
            'dry'         => 'உலர்',
            'wet'         => 'தர்',
            'liquid'      => 'திரவ',
            'sauce'       => 'சாஸ்',
            'curry'       => 'கறி',
            'indian'      => 'இந்திய',
            'south'       => 'தென்',
            'north'       => 'வட',
            'kerala'      => 'கேரள',
            'punjabi'     => 'பஞ்சாபி',
            'bengali'     => 'வங்காள',
            'mughal'      => 'முகலாய',

            // ── Sweets & desserts ──────────────────────────────────────────
            'halwa'       => 'அல்வா',
            'halva'       => 'அல்வா',
            'kesari'      => 'கேசரி',
            'kesarı'      => 'கேசரி',
            'laddu'       => 'லட்டு',
            'laddoo'      => 'லட்டு',
            'ladoo'       => 'லட்டு',
            'barfi'       => 'பர்ஃபி',
            'burfi'       => 'பர்ஃபி',
            'barfee'      => 'பர்ஃபி',
            'jangiri'     => 'ஜாங்கிரி',
            'jalebi'      => 'ஜிலேபி',
            'jilapi'      => 'ஜிலேபி',
            'mysore'      => 'மைசூர்',
            'mysorepak'   => 'மைசூர்பாக்',
            'pak'         => 'பாக்',
            'gulab'       => 'குலாப்',
            'jamun'       => 'ஜாமுன்',
            'rasgulla'    => 'ரஸ்குல்லா',
            'rasmalai'    => 'ரஸ்மலாய்',
            'kheer'       => 'கீர்',
            'payasam'     => 'பாயசம்',
            'payasa'      => 'பாயசம்',
            'semiya'      => 'சேமியா',
            'vermicelli'  => 'சேமியா',
            'poli'        => 'போளி',
            'puran'       => 'புரண்',
            'coconut'     => 'தேங்காய்',
            'moong'       => 'பாசி பருப்பு',
            'chana'       => 'கடலை',
            'badam'       => 'பாதாம்',
            'almond'      => 'பாதாம்',
            'cashew'      => 'முந்திரி',
            'pista'       => 'பிஸ்தா',
            'pistachio'   => 'பிஸ்தா',
            'kaju'        => 'முந்திரி',
            'katli'       => 'கட்லி',
            'cake'        => 'கேக்',
            'pudding'     => 'புட்டிங்',
            'khulfi'      => 'குல்ஃபி',
            'kulfi'       => 'குல்ஃபி',
            'brownie'     => 'பிரவுனி',
            'ice'         => 'ஐஸ்',
            'cream'       => 'கிரீம்',
            'sundae'      => 'சன்டே',
            'chocolate'   => 'சாக்லேட்',
            'vanilla'     => 'வெனிலா',
            'strawberry'  => 'ஸ்ட்ராபெரி',
            'mango'       => 'மாம்பழம்',

            // ── Savoury snacks & starters ──────────────────────────────────
            'samosa'      => 'சமோசா',
            'pakoda'      => 'பக்கோடா',
            'pakora'      => 'பக்கோரா',
            'vada'        => 'வடை',
            'bajji'       => 'பஜ்ஜி',
            'bhaji'       => 'பஜ்ஜி',
            'bonda'       => 'போண்டா',
            'chaat'       => 'சாட்',
            'panipuri'    => 'பானிபுரி',
            'pani'        => 'தண்ணீர்',
            'puri'        => 'பூரி',
            'chutney'     => 'சட்னி',
            'pickle'      => 'ஊறுகாய்',
            'papad'       => 'பப்படம்',
            'appalam'     => 'அப்பளம்',
            'chips'       => 'சிப்ஸ்',
            'murukku'     => 'முறுக்கு',
            'mixture'     => 'மிக்சர்',
            'peanut'      => 'கடலை',
            'groundnut'   => 'கடலை',
            'corn'        => 'சோளம்',
            'soup'        => 'சூப்',
            'salad'       => 'சாலட்',
            'raita'       => 'ரைத்தா',

            // ── Main course / rice dishes ──────────────────────────────────
            'rice'        => 'சாதம்',
            'biryani'     => 'பிரியாணி',
            'biriyani'    => 'பிரியாணி',
            'pulao'       => 'புலாவ்',
            'pulav'       => 'புலாவ்',
            'fried rice'  => 'ஃப்ரைட் ரைஸ்',
            'pongal'      => 'பொங்கல்',
            'ven pongal'  => 'வெண் பொங்கல்',
            'curd'        => 'தயிர்',
            'curd rice'   => 'தயிர் சாதம்',
            'lemon'       => 'எலுமிச்சை',
            'lemon rice'  => 'எலுமிச்சை சாதம்',
            'tamarind'    => 'புளி',
            'tomato'      => 'தக்காளி',
            'sambar'      => 'சாம்பார்',
            'rasam'       => 'ரசம்',
            'dal'         => 'பருப்பு',
            'dhal'        => 'பருப்பு',
            'kootu'       => 'கூட்டு',
            'poriyal'     => 'பொரியல்',
            'thoran'      => 'தோரன்',
            'aviyal'      => 'அவியல்',
            'moru'        => 'மோர் குழம்பு',
            'mor kuzhambu'=> 'மோர் குழம்பு',
            'paruppu'     => 'பருப்பு',
            'kuzhambu'    => 'குழம்பு',
            'vathal'      => 'வத்தல்',
            'vatha'       => 'வத்தல்',
            'manathakkali'=> 'மணத்தக்காளி',
            'thayir'      => 'தயிர்',
            'appam'       => 'அப்பம்',
            'string'      => 'ஸ்ட்ரிங்',
            'hoppers'     => 'ஆப்பம்',
            'puttu'       => 'புட்டு',
            'idiyappam'   => 'இடியாப்பம்',
            'upma'        => 'உப்மா',
            'pongala'     => 'பொங்கல்',

            // ── Breads ─────────────────────────────────────────────────────
            'chapati'     => 'சப்பாத்தி',
            'roti'        => 'ரொட்டி',
            'naan'        => 'நான்',
            'parotta'     => 'பரோட்டா',
            'paratha'     => 'பராத்தா',
            'poori'       => 'பூரி',
            'bhatura'     => 'பட்டூரி',

            // ── Idly / Dosa variants ────────────────────────────────────────
            'idly'        => 'இட்லி',
            'idli'        => 'இட்லி',
            'dosa'        => 'தோசை',
            'dosai'       => 'தோசை',
            'masala dosa' => 'மசாலா தோசை',
            'onion'       => 'வெங்காயம்',
            'tomato dosa' => 'தக்காளி தோசை',
            'rava'        => 'ரவா',
            'semolina'    => 'ரவா',
            'set dosa'    => 'செட் தோசை',
            'uthappam'    => 'உத்தப்பம்',
            'uttapam'     => 'உத்தப்பம்',
            'pesarattu'   => 'பேசரட்டு',

            // ── Non-veg items ──────────────────────────────────────────────
            'chicken'     => 'கோழி',
            'mutton'      => 'ஆட்டிறைச்சி',
            'lamb'        => 'ஆட்டிறைச்சி',
            'fish'        => 'மீன்',
            'prawn'       => 'இறால்',
            'shrimp'      => 'இறால்',
            'egg'         => 'முட்டை',
            'eggs'        => 'முட்டை',
            'crab'        => 'நண்டு',
            'squid'       => 'கணவாய்',
            'beef'        => 'மாட்டிறைச்சி',
            'pork'        => 'பன்றி',
            'kebab'       => 'கபாப்',
            'tikka'       => 'டிக்கா',
            'seekh'       => 'சீக்',

            // ── Vegetables ─────────────────────────────────────────────────
            'potato'      => 'உருளைக்கிழங்கு',
            'aloo'        => 'உருளைக்கிழங்கு',
            'spinach'     => 'கீரை',
            'palak'       => 'கீரை',
            'paneer'      => 'பனீர்',
            'gobi'        => 'காலிஃப்ளவர்',
            'cauliflower' => 'காலிஃப்ளவர்',
            'brinjal'     => 'கத்திரிக்காய்',
            'eggplant'    => 'கத்திரிக்காய்',
            'carrot'      => 'கேரட்',
            'beans'       => 'பீன்ஸ்',
            'peas'        => 'பட்டாணி',
            'mushroom'    => 'காளான்',
            'beetroot'    => 'பீட்ரூட்',
            'drumstick'   => 'முருங்கைக்காய்',
            'moringa'     => 'முருங்கை',
            'raw banana'  => 'வாழைக்காய்',
            'banana'      => 'வாழைப்பழம்',
            'raw'         => 'பச்சை',
            'yam'         => 'சேனைக்கிழங்கு',

            // ── Lentils & pulses ───────────────────────────────────────────
            'urad'        => 'உளுந்து',
            'toor'        => 'துவரம் பருப்பு',
            'masoor'      => 'மசூர் பருப்பு',
            'rajma'       => 'ராஜ்மா',
            'chickpea'    => 'கொண்டைக்கடலை',
            'chhole'      => 'சோளா',
            'chole'       => 'சோளா',

            // ── Dairy & paneer ─────────────────────────────────────────────
            'milk'        => 'பால்',
            'ghee'        => 'நெய்',
            'butter'      => 'வெண்ணெய்',
            'buttermilk'  => 'மோர்',
            'lassi'       => 'லஸ்ஸி',
            'chhena'      => 'சீஸ்',

            'cheese'      => 'சீஸ்',

            // ── Beverages & drinks ─────────────────────────────────────────
            'juice'       => 'சாறு',
            'drink'       => 'பானம்',
            'drinks'      => 'பானங்கள்',
            'water'       => 'தண்ணீர்',
            'tender'      => 'இளம்',
            'tender coconut' => 'இளநீர்',
            'sherbet'     => 'ஷர்பத்',
            'sharbat'     => 'ஷர்பத்',
            'rose'        => 'ரோஸ்',
            'badam milk'  => 'பாதாம் பால்',
            'coffee'      => 'காபி',
            'tea'         => 'தேநீர்',
            'chai'        => 'சாய்',
            'lemonade'    => 'எலுமிச்சைப் பானம்',
            'soda'        => 'சோடா',
            'cold drink'  => 'குளிர் பானம்',
            'welcome drink' => 'வரவேற்பு பானம்',
            'mocktail'    => 'மாக்டெயில்',

            // ── Fruits ─────────────────────────────────────────────────────
            'fruit'       => 'பழம்',
            'fruits'      => 'பழங்கள்',
            'apple'       => 'ஆப்பிள்',
            'grapes'      => 'திராட்சை',
            'orange'      => 'ஆரஞ்சு',
            'pineapple'   => 'அன்னாசி',
            'watermelon'  => 'தர்பூசணி',
            'papaya'      => 'பப்பாளி',
            'pomegranate' => 'மாதுளை',
            'guava'       => 'கொய்யா',
            'kiwi'        => 'கிவி',
            'pear'        => 'பேரிக்காய்',
            'sapota'      => 'சப்போட்டா',
            'chikoo'      => 'சப்போட்டா',
            'jackfruit'   => 'பலாப்பழம்',

            // ── Snacks & evening items ──────────────────────────────────────
            'poha'        => 'அவல்',
            'aval'        => 'அவல்',
            'sundal'      => 'சுண்டல்',
            'vadai'       => 'வடை',
            'sandwich'    => 'சாண்ட்விச்',
            'bread'       => 'பிரட்',
            'toast'       => 'டோஸ்ட்',
            'butter toast'=> 'வெண்ணெய் டோஸ்ட்',
            'omelet'      => 'ஆம்லட்',
            'omelette'    => 'ஆம்லட்',
            'noodles'     => 'நூடுல்ஸ்',
            'pasta'       => 'பாஸ்தா',
            'pizza'       => 'பிஸ்ஸா',
            'burger'      => 'பர்கர்',
            'roll'        => 'ரோல்',
            'wrap'        => 'ரேப்',
            'kati'        => 'கட்டி',
            'frankie'     => 'ஃப்ராங்கி',
            'spring roll' => 'ஸ்பிரிங் ரோல்',

            // ── Live counter / served items ─────────────────────────────────
            'live'        => 'லைவ்',
            'counter'     => 'கவுண்டர்',
            'station'     => 'ஸ்டேஷன்',
            'stall'       => 'கடை',
            'corner'      => 'மூலை',

            // ── Event / meal type labels ────────────────────────────────────
            'breakfast'   => 'காலை உணவு',
            'lunch'       => 'மதிய உணவு',
            'dinner'      => 'இரவு உணவு',
            'brunch'      => 'பிரஞ்ச்',
            'snacks'      => 'சிற்றுண்டி',
            'snack'       => 'சிற்றுண்டி',
            'evening snacks' => 'மாலை சிற்றுண்டி',
            'high tea'    => 'ஹை டீ',
            'midnight'    => 'நள்ளிரவு',
            'reception'   => 'வரவேற்பு',
            'menu'        => 'மெனு',
            'items'       => 'உணவுகள்',
            'item'        => 'உணவு',
            'starter'     => 'ஸ்டார்டர்',
            'starters'    => 'ஸ்டார்டர்கள்',
            'main'        => 'முக்கிய',
            'course'      => 'உணவு வகை',
            'dessert'     => 'இனிப்பு',
            'desserts'    => 'இனிப்புகள்',
            'beeda'       => 'பீடா',

            // ── Misc common modifiers ───────────────────────────────────────
            'with'        => 'உடன்',
            'without'     => 'இல்லாமல்',
            'and'         => 'மற்றும்',
            'or'          => 'அல்லது',
            'no'          => 'இல்லாத',
            'extra'       => 'கூடுதல்',
            'double'      => 'இரட்டிப்பு',
            'half'        => 'அரை',
            'full'        => 'முழு',
            'regular'     => 'சாதாரண',
            'medium'      => 'நடுத்தர',
            'small'       => 'சிறிய',
            'vip'         => 'விஐபி',
        ];
    }
}
