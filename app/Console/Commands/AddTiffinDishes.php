<?php

namespace App\Console\Commands;

use App\Models\MenuItem;
use Illuminate\Console\Command;

/**
 * Adds the South-Indian tiffin dishes (dosa varieties, idly, pongal, vada…) and common cauliflower
 * dishes to the dish list (/menu/items). Safe to re-run: a dish whose English name already exists
 * (case-insensitive) is skipped, never changed. New dishes reach the Customer Menu & Prices list
 * through MenuItemSync as INACTIVE at ₹0 — an admin sets the price and turns them on there.
 */
class AddTiffinDishes extends Command
{
    protected $signature = 'menu:add-tiffin-dishes {--dry-run : List what would be added without saving}';

    protected $description = 'Add tiffin (dosa varieties, idly, pongal, vada…) and cauliflower dishes to the dish list (safe to re-run)';

    /** [English, Tamil, category key] */
    public const DISHES = [
        // Dosa varieties
        ['Plain Dosa', 'சாதா தோசை', 'tiffin'],
        ['Masala Dosa', 'மசாலா தோசை', 'tiffin'],
        ['Onion Dosa', 'வெங்காய தோசை', 'tiffin'],
        ['Podi Dosa', 'பொடி தோசை', 'tiffin'],
        ['Ghee Roast Dosa', 'நெய் ரோஸ்ட் தோசை', 'tiffin'],
        ['Paper Roast Dosa', 'பேப்பர் ரோஸ்ட் தோசை', 'tiffin'],
        ['Rava Dosa', 'ரவா தோசை', 'tiffin'],
        ['Onion Rava Dosa', 'வெங்காய ரவா தோசை', 'tiffin'],
        ['Mysore Masala Dosa', 'மைசூர் மசாலா தோசை', 'tiffin'],
        ['Set Dosa', 'செட் தோசை', 'tiffin'],
        ['Kal Dosa', 'கல் தோசை', 'tiffin'],
        ['Egg Dosa', 'முட்டை தோசை', 'tiffin'],
        ['Onion Uttapam', 'வெங்காய ஊத்தப்பம்', 'tiffin'],
        // Other tiffin
        ['Idly', 'இட்லி', 'tiffin'],
        ['Mini Idly', 'மினி இட்லி', 'tiffin'],
        ['Podi Idly', 'பொடி இட்லி', 'tiffin'],
        ['Ven Pongal', 'வெண் பொங்கல்', 'tiffin'],
        ['Medu Vada', 'மெது வடை', 'tiffin'],
        ['Masal Vada', 'மசால் வடை', 'tiffin'],
        ['Rava Upma', 'ரவா உப்புமா', 'tiffin'],
        ['Semiya Upma', 'சேமியா உப்புமா', 'tiffin'],
        ['Poori Masala', 'பூரி மசாலா', 'tiffin'],
        ['Idiyappam', 'இடியாப்பம்', 'tiffin'],
        ['Appam', 'ஆப்பம்', 'tiffin'],
        ['Sambar', 'சாம்பார்', 'tiffin'],
        ['Coconut Chutney', 'தேங்காய் சட்னி', 'tiffin'],
        ['Tomato Chutney', 'தக்காளி சட்னி', 'tiffin'],
        ['Kara Chutney', 'கார சட்னி', 'tiffin'],
        ['Mint Chutney', 'புதினா சட்னி', 'tiffin'],
        // Cauliflower
        ['Gobi 65', 'கோபி 65', 'starter'],
        ['Cauliflower Pepper Fry', 'காலிஃபிளவர் மிளகு வறுவல்', 'dry_curry'],
        ['Gobi Masala', 'கோபி மசாலா', 'gravy'],
        ['Cauliflower Poriyal', 'காலிஃபிளவர் பொரியல்', 'poriyal'],
        ['Cauliflower Kurma', 'காலிஃபிளவர் குருமா', 'gravy'],
    ];

    public function handle(): int
    {
        $cats = config('menu_categories.items');
        $existing = MenuItem::pluck('item_en')->map(fn ($n) => mb_strtolower(trim($n)))->flip();
        $added = 0;
        foreach (self::DISHES as $i => [$en, $ta, $key]) {
            if ($existing->has(mb_strtolower($en))) {
                $this->line("  skip  {$en} (already in the dish list)");

                continue;
            }
            if (! $this->option('dry-run')) {
                MenuItem::create([
                    'category_key' => $key, 'category_en' => $cats[$key]['en'], 'category_ta' => $cats[$key]['ta'],
                    'item_en' => $en, 'item_ta' => $ta, 'sort_order' => $i, 'is_active' => true,
                ]);
            }
            $this->info('  add   '.$en.' — '.$cats[$key]['en']);
            $added++;
        }
        $this->info(($this->option('dry-run') ? 'Would add ' : 'Added ').$added.' dish(es). Set prices and turn them on in Customer Menu & Prices.');

        return self::SUCCESS;
    }
}
