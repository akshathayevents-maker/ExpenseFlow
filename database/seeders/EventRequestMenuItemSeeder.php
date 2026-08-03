<?php

namespace Database\Seeders;

use App\Models\EventRequestMenuCategory;
use App\Models\EventRequestMenuItem;
use Illuminate\Database\Seeder;

class EventRequestMenuItemSeeder extends Seeder
{
    /**
     * [name, description, is_veg, price_per_person]
     * Prices are hand-picked within each category's realistic band rather
     * than randomized, so the seeded menu always looks sane.
     */
    private function itemsByCategory(): array
    {
        return [
            'Welcome Drinks' => [
                ['Watermelon Juice', 'Chilled fresh watermelon juice', true, 20],
                ['Fresh Lime Soda', 'Sweet, salt, or plain', true, 18],
                ['Mango Panna', 'Tangy raw mango cooler', true, 25],
                ['Rose Milk', 'Chilled milk with rose syrup', true, 22],
                ['Tender Coconut Water', 'Served fresh in the shell', true, 35],
                ['Pineapple Punch', 'Sweet pineapple mocktail', true, 28],
                ['Mixed Fruit Mocktail', 'Seasonal fruit blend', true, 30],
                ['Blue Lagoon Mocktail', 'Blue curacao flavoured mocktail', true, 32],
                ['Virgin Mojito', 'Mint, lime, and soda', true, 30],
                ['Jaljeera', 'Spiced cumin cooler', true, 20],
                ['Buttermilk (Chaas)', 'Spiced yogurt drink', true, 18],
                ['Badam Milk', 'Almond flavoured chilled milk', true, 30],
                ['Fresh Orange Juice', 'Squeezed to order', true, 28],
                ['Grape Juice', 'Chilled fresh grape juice', true, 26],
                ['Nannari Sherbet', 'Sarsaparilla root cooler', true, 22],
                ['Ginger Ale Mocktail', 'Zesty ginger fizz', true, 25],
                ['Strawberry Cooler', 'Fresh strawberry crush', true, 30],
                ['Kokum Sherbet', 'Tangy kokum cooler', true, 20],
                ['Guava Juice', 'Fresh guava pulp juice', true, 24],
                ['Welcome Punch', 'House-special fruit punch', true, 30],
            ],
            'Soup' => [
                ['Tomato Soup', 'Classic creamy tomato soup', true, 28],
                ['Sweet Corn Soup', 'Veg sweet corn soup', true, 30],
                ['Hot & Sour Soup (Veg)', 'Spicy and tangy vegetable soup', true, 30],
                ['Manchow Soup (Veg)', 'Indo-Chinese spiced vegetable soup', true, 32],
                ['Cream of Mushroom Soup', 'Rich and creamy mushroom soup', true, 38],
                ['Lemon Coriander Soup', 'Light and zesty clear soup', true, 26],
                ['Spinach Corn Soup', 'Spinach and sweet corn soup', true, 32],
                ['Vegetable Clear Soup', 'Light broth with fresh vegetables', true, 25],
                ['Broccoli Almond Soup', 'Creamy broccoli with almond flakes', true, 40],
                ['Carrot Ginger Soup', 'Roasted carrot and ginger soup', true, 32],
                ['Minestrone Soup', 'Italian vegetable and pasta soup', true, 38],
                ['Chicken Clear Soup', 'Light chicken broth', false, 35],
                ['Hot & Sour Chicken Soup', 'Spicy and tangy chicken soup', false, 38],
                ['Chicken Manchow Soup', 'Indo-Chinese spiced chicken soup', false, 40],
                ['Mutton Soup', 'Slow-cooked mutton bone broth', false, 45],
                ['Chicken Sweet Corn Soup', 'Shredded chicken and sweet corn', false, 38],
                ['Pepper Chicken Soup', 'Peppery South Indian style soup', false, 40],
                ['Fish Soup', 'Light fish stock soup', false, 42],
                ['Prawn Soup', 'Delicate prawn broth', false, 45],
                ['Egg Drop Soup', 'Silky egg ribbons in broth', false, 30],
            ],
            'Starter' => [
                ['Paneer Tikka', 'Char-grilled marinated cottage cheese', true, 85],
                ['Hara Bhara Kabab', 'Spinach and green pea patties', true, 65],
                ['Gobi Manchurian', 'Crispy cauliflower in tangy sauce', true, 70],
                ['Veg Spring Roll', 'Crisp fried rolls with vegetable filling', true, 60],
                ['Baby Corn Fry', 'Crispy fried baby corn', true, 60],
                ['Mushroom Pepper Fry', 'Peppery sauteed mushrooms', true, 75],
                ['Paneer 65', 'Spicy deep-fried cottage cheese', true, 90],
                ['Crispy Corn Chaat', 'Sweet corn tossed in tangy masala', true, 55],
                ['Veg Seekh Kabab', 'Skewered mixed vegetable kabab', true, 70],
                ['Chilli Paneer', 'Indo-Chinese spiced paneer', true, 90],
                ['Chicken 65', 'Spicy deep-fried chicken', false, 110],
                ['Chicken Tikka', 'Char-grilled marinated chicken', false, 115],
                ['Fish Fry', 'Crispy shallow-fried fish', false, 120],
                ['Prawn Fry', 'Golden fried spiced prawns', false, 120],
                ['Chicken Lollipop', 'Frenched chicken wings, deep-fried', false, 110],
                ['Mutton Seekh Kabab', 'Skewered minced mutton kabab', false, 120],
                ['Chilli Chicken', 'Indo-Chinese spiced chicken', false, 110],
                ['Tandoori Chicken', 'Clay-oven roasted chicken', false, 120],
                ['Fish Tikka', 'Char-grilled marinated fish', false, 120],
                ['Prawn Koliwada', 'Crispy spiced fried prawns', false, 120],
            ],
            'Main Course' => [
                ['Paneer Butter Masala', 'Rich creamy paneer cooked slowly', true, 95],
                ['Kadai Paneer', 'Paneer in a spiced kadai masala', true, 95],
                ['Malai Kofta', 'Fried vegetable dumplings in creamy gravy', true, 100],
                ['Dal Makhani', 'Slow-cooked black lentils in butter and cream', true, 80],
                ['Veg Kurma', 'Mixed vegetables in coconut gravy', true, 75],
                ['Channa Masala', 'Spiced chickpea curry', true, 65],
                ['Aloo Gobi', 'Dry-spiced potato and cauliflower', true, 60],
                ['Palak Paneer', 'Cottage cheese in creamy spinach gravy', true, 90],
                ['Vegetable Korma', 'Mixed vegetables in a mild creamy sauce', true, 75],
                ['Mixed Veg Curry', 'Seasonal vegetables in onion-tomato gravy', true, 65],
                ['Chicken Butter Masala', 'Chicken in rich buttery tomato gravy', false, 150],
                ['Mutton Curry', 'Slow-cooked traditional mutton curry', false, 180],
                ['Chicken Chettinad', 'Spicy South Indian style chicken', false, 150],
                ['Fish Curry', 'Tangy coastal-style fish curry', false, 160],
                ['Prawn Masala', 'Prawns in a spiced onion-tomato masala', false, 170],
                ['Chicken Chukka', 'Dry-roasted spiced chicken', false, 150],
                ['Egg Curry', 'Boiled eggs in spiced gravy', false, 70],
                ['Kadai Chicken', 'Chicken in a spiced kadai masala', false, 150],
                ['Chicken Korma', 'Chicken in a mild creamy sauce', false, 150],
                ['Mutton Chukka', 'Dry-roasted spiced mutton', false, 180],
            ],
            'Gravy' => [
                ['Veg Kolhapuri', 'Spicy mixed vegetables, Kolhapuri style', true, 75],
                ['Paneer Lababdar', 'Paneer in a rich tomato-cashew gravy', true, 100],
                ['Methi Malai Matar', 'Fenugreek, peas, and cream curry', true, 80],
                ['Bhindi Masala', 'Okra cooked in onion-tomato masala', true, 65],
                ['Baingan Bharta', 'Smoky roasted mashed eggplant curry', true, 65],
                ['Aloo Matar', 'Potatoes and peas in light gravy', true, 60],
                ['Chana Dal Tadka', 'Tempered split chickpea lentils', true, 60],
                ['Veg Handi', 'Mixed vegetables slow-cooked in handi', true, 80],
                ['Paneer Do Pyaza', 'Paneer with onions in thick gravy', true, 95],
                ['Vegetable Jalfrezi', 'Stir-fried vegetables in tangy sauce', true, 70],
                ['Chicken Handi', 'Chicken slow-cooked in traditional handi', false, 155],
                ['Mutton Rogan Josh', 'Kashmiri style aromatic mutton curry', false, 180],
                ['Chicken Do Pyaza', 'Chicken with onions in thick gravy', false, 150],
                ['Fish Moilee', 'Kerala style fish in coconut gravy', false, 165],
                ['Chicken Kolhapuri', 'Spicy chicken, Kolhapuri style', false, 155],
                ['Mutton Kolhapuri', 'Spicy mutton, Kolhapuri style', false, 180],
                ['Chicken Angara', 'Smoky charcoal-finished chicken curry', false, 160],
                ['Prawn Curry', 'Prawns in a coconut-based curry', false, 175],
                ['Chicken Saagwala', 'Chicken cooked in spinach gravy', false, 150],
                ['Mutton Do Pyaza', 'Mutton with onions in thick gravy', false, 180],
            ],
            'Rice' => [
                ['Jeera Rice', 'Cumin-tempered steamed rice', true, 40],
                ['Veg Biryani', 'Fragrant rice layered with vegetables', true, 75],
                ['Curd Rice', 'Comforting yogurt rice with tempering', true, 35],
                ['Lemon Rice', 'Tangy South Indian tempered rice', true, 35],
                ['Tamarind Rice', 'Tangy Puliyodarai style rice', true, 35],
                ['Coconut Rice', 'Rice tempered with fresh coconut', true, 35],
                ['Vegetable Pulao', 'Mildly spiced rice with vegetables', true, 55],
                ['Ghee Rice', 'Fragrant rice cooked in ghee', true, 40],
                ['Tomato Rice', 'Tangy tomato tempered rice', true, 35],
                ['Bisi Bele Bath', 'Karnataka style spiced rice and lentils', true, 50],
                ['Chicken Biryani', 'Layered rice with marinated chicken', false, 150],
                ['Mutton Biryani', 'Layered rice with slow-cooked mutton', false, 180],
                ['Egg Biryani', 'Layered rice with spiced boiled eggs', false, 80],
                ['Prawn Biryani', 'Layered rice with spiced prawns', false, 170],
                ['Fish Biryani', 'Layered rice with marinated fish', false, 160],
            ],
            'Indian Bread' => [
                ['Butter Naan', 'Soft tandoor-baked bread with butter', true, 15],
                ['Garlic Naan', 'Naan topped with fresh garlic and herbs', true, 18],
                ['Tandoori Roti', 'Whole wheat bread from the tandoor', true, 10],
                ['Chapati', 'Soft whole wheat flatbread', true, 8],
                ['Onion Kulcha', 'Stuffed onion-flavoured bread', true, 18],
                ['Paneer Kulcha', 'Stuffed cottage cheese bread', true, 20],
                ['Missi Roti', 'Spiced gram flour flatbread', true, 12],
                ['Lachha Paratha', 'Multi-layered flaky flatbread', true, 15],
                ['Plain Paratha', 'Layered whole wheat flatbread', true, 12],
                ['Aloo Paratha', 'Potato-stuffed flatbread', true, 18],
                ['Bullet Naan', 'Naan stuffed with green chillies', true, 18],
                ['Cheese Naan', 'Naan stuffed with melted cheese', true, 20],
                ['Roomali Roti', 'Paper-thin handkerchief bread', true, 10],
                ['Stuffed Kulcha', 'Mixed vegetable stuffed bread', true, 18],
                ['Phulka', 'Light puffed whole wheat bread', true, 8],
            ],
            'Dessert' => [
                ['Gulab Jamun', 'Soft milk dumplings in sugar syrup', true, 30],
                ['Rasgulla', 'Spongy cheese balls in light syrup', true, 30],
                ['Rasmalai', 'Cheese patties in sweetened milk', true, 40],
                ['Kesari Halwa', 'Saffron flavoured semolina halwa', true, 28],
                ['Gajar Halwa', 'Slow-cooked carrot halwa with khoya', true, 35],
                ['Jalebi', 'Crispy syrup-soaked spirals', true, 25],
                ['Kheer', 'Traditional rice pudding', true, 30],
                ['Payasam', 'South Indian style sweet pudding', true, 30],
                ['Moong Dal Halwa', 'Rich lentil halwa with ghee', true, 45],
                ['Double Ka Meetha', 'Hyderabadi bread pudding', true, 40],
                ['Mysore Pak', 'Ghee-rich gram flour sweet', true, 35],
                ['Badam Halwa', 'Rich almond halwa', true, 50],
                ['Semiya Payasam', 'Vermicelli pudding with nuts', true, 30],
                ['Chocolate Brownie', 'Warm fudgy chocolate brownie', true, 45],
                ['Fruit Custard', 'Chilled custard with seasonal fruits', true, 35],
                ['Ras Malai Cheesecake', 'Fusion rasmalai flavoured cheesecake', true, 55],
                ['Coconut Barfi', 'Sweet coconut fudge squares', true, 28],
                ['Kaju Katli', 'Cashew fudge diamonds', true, 60],
                ['Motichoor Ladoo', 'Fine gram flour pearl sweets', true, 30],
                ['Sandesh', 'Delicate Bengali cottage cheese sweet', true, 35],
            ],
            'Ice Cream' => [
                ['Vanilla', 'Classic vanilla bean ice cream', true, 20],
                ['Strawberry', 'Fresh strawberry ice cream', true, 22],
                ['Chocolate', 'Rich chocolate ice cream', true, 22],
                ['Butterscotch', 'Caramelised butterscotch ice cream', true, 25],
                ['Black Currant', 'Tangy black currant ice cream', true, 25],
                ['Mango', 'Seasonal fresh mango ice cream', true, 28],
                ['Pista', 'Roasted pistachio ice cream', true, 30],
                ['Kesar Pista', 'Saffron and pistachio ice cream', true, 32],
                ['Rajbhog', 'Saffron, nuts, and rose ice cream', true, 35],
                ['American Nuts', 'Mixed nut crunch ice cream', true, 30],
                ['Tender Coconut', 'Fresh tender coconut ice cream', true, 28],
                ['Anjeer', 'Fig and dry fruit ice cream', true, 32],
                ['Belgian Chocolate', 'Premium dark chocolate ice cream', true, 40],
                ['Rum n Raisin', 'Raisins soaked in rum flavour', true, 35],
                ['Fig & Honey', 'Fig swirl with honey drizzle', true, 38],
                ['Blueberry', 'Fresh blueberry compote swirl', true, 30],
                ['Alphonso Mango', 'Premium Alphonso mango ice cream', true, 40],
                ['Cookies n Cream', 'Vanilla ice cream with cookie chunks', true, 30],
                ['Roasted Almond', 'Roasted almond crunch ice cream', true, 32],
                ['Litchi', 'Fresh litchi flavoured ice cream', true, 28],
            ],
            'Beverage' => [
                ['Masala Chai', 'Spiced Indian tea', true, 15],
                ['Filter Coffee', 'South Indian style filter coffee', true, 18],
                ['Cold Coffee', 'Chilled blended coffee', true, 30],
                ['Iced Tea', 'Chilled lemon iced tea', true, 25],
                ['Lemon Iced Tea', 'Refreshing citrus iced tea', true, 25],
                ['Green Tea', 'Light and refreshing green tea', true, 18],
                ['Hot Chocolate', 'Rich warm chocolate drink', true, 30],
                ['Sugarcane Juice', 'Freshly pressed sugarcane juice', true, 25],
                ['Coconut Water Chilled', 'Chilled tender coconut water', true, 30],
                ['Buttermilk Spiced', 'Spiced yogurt drink', true, 18],
                ['Fresh Orange Juice (Beverage Counter)', 'Squeezed to order', true, 28],
                ['Watermelon Cooler', 'Chilled watermelon refresher', true, 22],
                ['Mint Lemonade', 'Fresh mint and lime cooler', true, 22],
                ['Espresso', 'Single shot of strong coffee', true, 20],
                ['Cappuccino', 'Espresso with steamed milk foam', true, 30],
                ['Cafe Latte', 'Smooth espresso with steamed milk', true, 32],
                ['Hot Bournvita', 'Warm malted chocolate drink', true, 20],
                ['Rose Sherbet', 'Chilled rose flavoured sherbet', true, 20],
                ['Kokum Cooler', 'Tangy kokum based cooler', true, 20],
                ['Ginger Lemon Tea', 'Zesty ginger and lemon tea', true, 18],
            ],
        ];
    }

    public function run(): void
    {
        $categories = EventRequestMenuCategory::pluck('id', 'name');

        foreach ($this->itemsByCategory() as $categoryName => $items) {
            $categoryId = $categories[$categoryName] ?? null;

            if (! $categoryId) {
                continue;
            }

            foreach ($items as $index => [$name, $description, $isVeg, $price]) {
                // Deterministic-looking "popular"/"chef recommended" spread
                // rather than random, so re-seeding always looks the same.
                $isPopular = $index % 5 === 0;
                $isChefRecommended = $index % 7 === 3;

                EventRequestMenuItem::updateOrCreate(
                    ['category_id' => $categoryId, 'name' => $name],
                    [
                        'description'          => $description,
                        'is_veg'               => $isVeg,
                        'price_per_person'     => $price,
                        'is_popular'           => $isPopular,
                        'is_chef_recommended'  => $isChefRecommended,
                        'display_order'        => $index + 1,
                        'is_active'            => true,
                    ]
                );
            }
        }
    }
}
