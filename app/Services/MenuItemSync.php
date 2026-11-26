<?php

namespace App\Services;

use App\Models\EventRequestMenuCategory;
use App\Models\EventRequestMenuItem;
use App\Models\MenuItem;
use Illuminate\Support\Str;

/**
 * Keeps the Menu Composer item master (menu_items) and the Event Request menu (event_request_menu_items) as the
 * same list of dishes. Synced both ways: name + category (and new / removed dishes).
 * Not synced: price (event request only), Tamil name (menu only), active flag (each page decides where it is offered).
 * A dish created on the menu page is added to the event request menu INACTIVE at ₹0, so customers never see an
 * unpriced dish until an admin sets the price and activates it.
 */
class MenuItemSync
{
    private static bool $running = false;

    /** Event request category name → menu category key (config/menu_categories.php). */
    private const CATEGORY_MAP = [
        'welcome drinks' => 'welcome_drink', 'welcome drink' => 'welcome_drink', 'soup' => 'soup', 'starter' => 'starter',
        'starters' => 'starter', 'main course' => 'main_course', 'gravy' => 'gravy', 'rice' => 'rice', 'indian bread' => 'indian_bread',
        'breads' => 'indian_bread', 'dessert' => 'dessert', 'desserts' => 'dessert', 'sweet' => 'sweet', 'sweets' => 'sweet',
        'ice cream' => 'ice_cream', 'beverage' => 'beverages', 'beverages' => 'beverages', 'salad' => 'salad', 'snacks' => 'evening_snacks',
    ];

    public function __construct(private MenuTranslationService $translate) {}

    public static function quietly(callable $fn): mixed
    {
        $was = self::$running;
        self::$running = true;
        try {
            return $fn();
        } finally {
            self::$running = $was;
        }
    }

    public static function busy(): bool
    {
        return self::$running;
    }

    public function categoryKeyFor(?string $erCategory): string
    {
        $name = mb_strtolower(trim((string) $erCategory));
        $keys = config('menu_categories.items', []);
        $key = self::CATEGORY_MAP[$name] ?? Str::snake($name);

        return isset($keys[$key]) ? $key : 'other';
    }

    public function erCategoryFor(string $key): EventRequestMenuCategory
    {
        $wanted = array_keys(array_filter(self::CATEGORY_MAP, fn ($k) => $k === $key));
        $label = config("menu_categories.items.$key.en") ?? 'Other';
        $cat = EventRequestMenuCategory::get()->first(fn ($c) => in_array(mb_strtolower($c->name), $wanted, true)
            || mb_strtolower($c->name) === mb_strtolower($label));
        // "Sweet" has no event-request category of its own → Dessert; otherwise create the category (inactive items only)
        if (! $cat && $key === 'sweet') {
            $cat = EventRequestMenuCategory::whereRaw('LOWER(name) = ?', ['dessert'])->first();
        }

        return $cat ?? EventRequestMenuCategory::create(['name' => $label, 'display_order' => 99, 'is_active' => true]);
    }

    /** Event request dish saved → create / update its menu item. */
    public function fromEventRequest(EventRequestMenuItem $er): MenuItem
    {
        return self::quietly(function () use ($er) {
            $er->load('category');   // fresh: the category may just have changed
            $key = $this->categoryKeyFor($er->category?->name);
            $cat = config("menu_categories.items.$key");
            $menu = MenuItem::where('event_request_menu_item_id', $er->id)->first()
                ?? MenuItem::whereNull('event_request_menu_item_id')->whereRaw('LOWER(item_en) = ?', [mb_strtolower(trim($er->name))])->first();
            $fields = ['event_request_menu_item_id' => $er->id, 'item_en' => trim($er->name), 'category_key' => $key,
                'category_en' => $cat['en'], 'category_ta' => $cat['ta']];
            if ($menu) {
                $menu->update($fields);

                return $menu;
            }

            return MenuItem::create($fields + [
                'item_ta' => $this->tamilFor($er->name),
                'sort_order' => (int) $er->display_order, 'is_active' => (bool) $er->is_active,
            ]);
        });
    }

    /** Menu item saved → create / update its event request dish (new ones inactive, ₹0). */
    public function fromMenu(MenuItem $menu): EventRequestMenuItem
    {
        return self::quietly(function () use ($menu) {
            $cat = $this->erCategoryFor($menu->category_key);
            $er = $menu->event_request_menu_item_id ? EventRequestMenuItem::find($menu->event_request_menu_item_id) : null;
            $er ??= EventRequestMenuItem::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($menu->item_en))])
                ->whereDoesntHave('menuItemMirror')->first();
            if ($er) {
                $er->update(['name' => trim($menu->item_en), 'category_id' => $cat->id]);
            } else {
                $er = EventRequestMenuItem::create(['category_id' => $cat->id, 'name' => trim($menu->item_en), 'price_per_person' => 0,
                    'is_veg' => true, 'display_order' => (int) $menu->sort_order, 'is_active' => false]);
            }
            if ($menu->event_request_menu_item_id !== $er->id) {
                $menu->forceFill(['event_request_menu_item_id' => $er->id])->saveQuietly();
            }

            return $er;
        });
    }

    /** Dictionary translation only when it is fully Tamil; a half-translated mix ("புதிய Lime சோடா") is worse than English. */
    public function tamilFor(string $english): string
    {
        $ta = $this->translate->translate($english);

        return $ta && ! preg_match('/[A-Za-z]/', $ta) ? $ta : $english;
    }

    /** Make both lists identical. Safe to run repeatedly. @return array{menu_created:int, menu_linked:int, er_created:int} */
    public function syncAll(): array
    {
        $before = ['menu' => MenuItem::count(), 'er' => EventRequestMenuItem::count()];
        foreach (EventRequestMenuItem::with('category')->orderBy('id')->get() as $er) {
            $this->fromEventRequest($er);
        }
        foreach (MenuItem::whereNull('event_request_menu_item_id')->get() as $menu) {
            $this->fromMenu($menu);
        }

        return ['menu_created' => MenuItem::count() - $before['menu'], 'er_created' => EventRequestMenuItem::count() - $before['er'],
            'linked' => MenuItem::whereNotNull('event_request_menu_item_id')->count(), 'total_menu' => MenuItem::count(), 'total_er' => EventRequestMenuItem::count()];
    }
}
