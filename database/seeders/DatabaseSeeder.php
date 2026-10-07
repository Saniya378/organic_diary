<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Deal;
use App\Models\DealDelivery;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Settings
        Setting::set('delivery_fee', '150');
        Setting::set('commission_percent', '5');
        Setting::set('low_stock_threshold', '10');
        Setting::set('allowed_cities', json_encode(['Gilgit', 'Skardu', 'Hunza', 'Ghizer', 'Astore', 'Nagar', 'Diamer']));

        // 2. Admin
        $admin = User::create([
            'name' => 'Admin Gilgit',
            'email' => 'admin@organicdairy.test',
            'phone' => '03551234567',
            'password' => Hash::make('Admin@12345'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 3. Approved Sellers
        $sellerHunza = User::create([
            'name' => 'Hunza Valley Farm',
            'email' => 'hunza@organicdairy.test',
            'phone' => '03555551111',
            'password' => Hash::make('Seller@12345'),
            'role' => 'seller',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        SellerProfile::create([
            'user_id' => $sellerHunza->id,
            'farm_name' => 'Hunza Valley Farm',
            'location' => 'Karimabad, Hunza',
            'description' => 'Pure high-altitude Himalayan organic milk, churned butter, and artisan cheese produced using traditional sustainable practices.',
            'photo_path' => 'farms/hunza.jpg',
            'status' => 'approved',
        ]);

        $sellerSkardu = User::create([
            'name' => 'Skardu Farmers Co-op',
            'email' => 'skardu@organicdairy.test',
            'phone' => '03555552222',
            'password' => Hash::make('Seller@12345'),
            'role' => 'seller',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        SellerProfile::create([
            'user_id' => $sellerSkardu->id,
            'farm_name' => 'Skardu Farmers Co-op',
            'location' => 'Sadpara Road, Skardu',
            'description' => 'Cooperative of Baltistan mountain herders providing organic yak, cow, and goat dairy products straight from glacier valleys.',
            'photo_path' => 'farms/skardu.jpg',
            'status' => 'approved',
        ]);

        $sellerGhizer = User::create([
            'name' => 'Ghizer Organic Dairy',
            'email' => 'ghizer@organicdairy.test',
            'phone' => '03555553333',
            'password' => Hash::make('Seller@12345'),
            'role' => 'seller',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        SellerProfile::create([
            'user_id' => $sellerGhizer->id,
            'farm_name' => 'Ghizer Organic Dairy',
            'location' => 'Gahkuch, Ghizer',
            'description' => 'Fresh artisan dairy products from the lush green pastures along the Ghizer river.',
            'photo_path' => 'farms/ghizer.jpg',
            'status' => 'approved',
        ]);

        // 4. Pending Sellers
        $sellerAstore = User::create([
            'name' => 'Astore Valley Farm',
            'email' => 'astore@organicdairy.test',
            'phone' => '03555554444',
            'password' => Hash::make('Seller@12345'),
            'role' => 'seller',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        SellerProfile::create([
            'user_id' => $sellerAstore->id,
            'farm_name' => 'Astore Valley Farm',
            'location' => 'Gorikot, Astore',
            'description' => 'High pasture natural organic ghee and butter sourced from Rama and Deosai fringes.',
            'photo_path' => 'farms/astore.jpg',
            'status' => 'pending',
        ]);

        $sellerNagar = User::create([
            'name' => 'Nagar Mountain Dairy',
            'email' => 'nagar@organicdairy.test',
            'phone' => '03555555555',
            'password' => Hash::make('Seller@12345'),
            'role' => 'seller',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        SellerProfile::create([
            'user_id' => $sellerNagar->id,
            'farm_name' => 'Nagar Mountain Dairy',
            'location' => 'Chalt, Nagar',
            'description' => 'Fresh local mountain milk, yogurt, and paneer straight from Nagar valley meadows.',
            'photo_path' => 'farms/nagar.jpg',
            'status' => 'pending',
        ]);

        // 5. Buyers with Addresses
        $buyerAli = User::create([
            'name' => 'Ali Khan',
            'email' => 'buyer@organicdairy.test',
            'phone' => '03451234567',
            'password' => Hash::make('Buyer@12345'),
            'role' => 'buyer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $buyerAliAddress1 = Address::create([
            'user_id' => $buyerAli->id,
            'full_name' => 'Ali Khan',
            'phone' => '03451234567',
            'city' => 'Gilgit',
            'area' => 'Jutial',
            'address_line' => 'House 14, River View Road, Near KIU',
            'is_default' => true,
        ]);
        Address::create([
            'user_id' => $buyerAli->id,
            'full_name' => 'Ali Khan',
            'phone' => '03451234567',
            'city' => 'Hunza',
            'area' => 'Aliabad',
            'address_line' => 'Bazaar Road, Near Bank, Aliabad',
            'is_default' => false,
        ]);

        $buyerFatima = User::create([
            'name' => 'Fatima Zahra',
            'email' => 'fatima@organicdairy.test',
            'phone' => '03129876543',
            'password' => Hash::make('Buyer@12345'),
            'role' => 'buyer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $buyerFatimaAddress = Address::create([
            'user_id' => $buyerFatima->id,
            'full_name' => 'Fatima Zahra',
            'phone' => '03129876543',
            'city' => 'Skardu',
            'area' => 'Hameed Garh',
            'address_line' => 'House 5, Hospital Road, Skardu',
            'is_default' => true,
        ]);

        // 6. Categories (7 Categories)
        $categoriesData = [
            ['name' => 'Milk', 'slug' => 'milk'],
            ['name' => 'Butter', 'slug' => 'butter'],
            ['name' => 'Ghee', 'slug' => 'ghee'],
            ['name' => 'Yogurt', 'slug' => 'yogurt'],
            ['name' => 'Cheese', 'slug' => 'cheese'],
            ['name' => 'Paneer', 'slug' => 'paneer'],
            ['name' => 'Cream', 'slug' => 'cream'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::create([
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'is_active' => true,
            ]);
        }

        // 7. Products and Variants (~24 approved products + 2 pending products)
        $productsSeed = [
            // MILK
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['milk']->id,
                'name' => 'Fresh Cow Milk 1 Litre',
                'slug' => 'fresh-cow-milk-1-litre',
                'description' => 'Pure and untreated fresh cow milk from free-grazing Himalayan cows in Hunza Valley.',
                'price' => 220,
                'unit' => '1 Litre',
                'stock' => 45,
                'batch_no' => 'HNZ-MLK-01',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '1 Litre Bottle', 'price' => 220, 'stock' => 30],
                    ['label' => '2 Litre Family Pack', 'price' => 430, 'stock' => 15],
                ],
            ],
            [
                'seller_id' => $sellerSkardu->id,
                'category_id' => $categories['milk']->id,
                'name' => 'Pure Mountain Yak Milk',
                'slug' => 'pure-mountain-yak-milk',
                'description' => 'Rich, dense, and nutrient-packed raw yak milk from the high alpine meadows of Baltistan.',
                'price' => 350,
                'unit' => '1 Litre',
                'stock' => 20,
                'batch_no' => 'SKD-YAK-02',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '1 Litre', 'price' => 350, 'stock' => 15],
                    ['label' => '2 Litre', 'price' => 680, 'stock' => 5],
                ],
            ],
            [
                'seller_id' => $sellerGhizer->id,
                'category_id' => $categories['milk']->id,
                'name' => 'Pasture Fresh Goat Milk',
                'slug' => 'pasture-fresh-goat-milk',
                'description' => 'Easy to digest and naturally homogenized organic goat milk from Ghizer hills.',
                'price' => 280,
                'unit' => '1 Litre',
                'stock' => 25,
                'batch_no' => 'GHZ-GT-01',
                'produced_on' => Carbon::today()->subDays(2),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '1 Litre', 'price' => 280, 'stock' => 18],
                    ['label' => '2 Litre', 'price' => 540, 'stock' => 7],
                ],
            ],
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['milk']->id,
                'name' => 'Raw Jersey Cow Milk',
                'slug' => 'raw-jersey-cow-milk',
                'description' => 'High butterfat creamy milk from grass-fed Jersey breed cows in Nagar-Hunza border farms.',
                'price' => 240,
                'unit' => '1 Litre',
                'stock' => 18,
                'batch_no' => 'HNZ-JRS-03',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '1 Litre', 'price' => 240, 'stock' => 12],
                    ['label' => '2 Litre', 'price' => 460, 'stock' => 6],
                ],
            ],

            // BUTTER
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['butter']->id,
                'name' => 'Desi Butter 250 g',
                'slug' => 'desi-butter-250-g',
                'description' => 'Traditionally churned aromatic desi butter with no preservatives or artificial colors.',
                'price' => 650,
                'unit' => '250 g',
                'stock' => 35,
                'batch_no' => 'HNZ-BTR-01',
                'produced_on' => Carbon::today()->subDays(3),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '250 g Tub', 'price' => 650, 'stock' => 20],
                    ['label' => '500 g Tub', 'price' => 1250, 'stock' => 15],
                ],
            ],
            [
                'seller_id' => $sellerSkardu->id,
                'category_id' => $categories['butter']->id,
                'name' => 'Cultured Mountain Butter',
                'slug' => 'cultured-mountain-butter',
                'description' => 'Fermented cultured butter made from naturally soured cream in traditional Balti wooden barrels.',
                'price' => 700,
                'unit' => '250 g',
                'stock' => 16,
                'batch_no' => 'SKD-BTR-02',
                'produced_on' => Carbon::today()->subDays(4),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '250 g', 'price' => 700, 'stock' => 10],
                    ['label' => '500 g', 'price' => 1350, 'stock' => 6],
                ],
            ],
            [
                'seller_id' => $sellerGhizer->id,
                'category_id' => $categories['butter']->id,
                'name' => 'Salted Herb Butter',
                'slug' => 'salted-herb-butter',
                'description' => 'Artisanal organic butter blended with wild mountain mint and Himalayan pink salt.',
                'price' => 680,
                'unit' => '250 g',
                'stock' => 12,
                'batch_no' => 'GHZ-BTR-03',
                'produced_on' => Carbon::today()->subDays(3),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '250 g', 'price' => 680, 'stock' => 12],
                ],
            ],
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['butter']->id,
                'name' => 'Unsalted Farm Butter',
                'slug' => 'unsalted-farm-butter',
                'description' => 'Pure sweet cream unsalted butter, ideal for baking and daily family breakfast.',
                'price' => 620,
                'unit' => '250 g',
                'stock' => 22,
                'batch_no' => 'HNZ-BTR-04',
                'produced_on' => Carbon::today()->subDays(2),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '250 g', 'price' => 620, 'stock' => 14],
                    ['label' => '500 g', 'price' => 1200, 'stock' => 8],
                ],
            ],

            // GHEE
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['ghee']->id,
                'name' => 'Pure Desi Ghee 500 g',
                'slug' => 'pure-desi-ghee-500-g',
                'description' => 'Slow-simmered golden desi ghee crafted using traditional bilona method from organic curd.',
                'price' => 1850,
                'unit' => '500 g',
                'stock' => 40,
                'batch_no' => 'HNZ-GHEE-01',
                'produced_on' => Carbon::today()->subDays(5),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '500 g Glass Jar', 'price' => 1850, 'stock' => 25],
                    ['label' => '1 kg Glass Jar', 'price' => 3600, 'stock' => 15],
                ],
            ],
            [
                'seller_id' => $sellerSkardu->id,
                'category_id' => $categories['ghee']->id,
                'name' => 'Baltistan Yak Ghee',
                'slug' => 'baltistan-yak-ghee',
                'description' => 'Rare, intensely fragrant, and nutrient-dense clarified butter made exclusively from organic yak milk.',
                'price' => 2400,
                'unit' => '500 g',
                'stock' => 8, // low stock
                'batch_no' => 'SKD-YGHEE-02',
                'produced_on' => Carbon::today()->subDays(6),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '500 g Jar', 'price' => 2400, 'stock' => 5],
                    ['label' => '1 kg Jar', 'price' => 4700, 'stock' => 3],
                ],
            ],
            [
                'seller_id' => $sellerGhizer->id,
                'category_id' => $categories['ghee']->id,
                'name' => 'Traditional Churn Ghee',
                'slug' => 'traditional-churn-ghee',
                'description' => 'Granular and aromatic ghee prepared on slow woodfire in earthenware pots.',
                'price' => 1950,
                'unit' => '500 g',
                'stock' => 28,
                'batch_no' => 'GHZ-GHEE-03',
                'produced_on' => Carbon::today()->subDays(4),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '500 g', 'price' => 1950, 'stock' => 18],
                    ['label' => '1 kg', 'price' => 3800, 'stock' => 10],
                ],
            ],
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['ghee']->id,
                'name' => 'Golden Herb-Fed Cow Ghee',
                'slug' => 'golden-herb-fed-cow-ghee',
                'description' => 'Premium bright golden clarified butter from cows grazing on wild lavender and medicinal mountain herbs.',
                'price' => 2100,
                'unit' => '500 g',
                'stock' => 15,
                'batch_no' => 'HNZ-GHEE-04',
                'produced_on' => Carbon::today()->subDays(7),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '500 g', 'price' => 2100, 'stock' => 10],
                    ['label' => '1 kg', 'price' => 4100, 'stock' => 5],
                ],
            ],

            // YOGURT
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['yogurt']->id,
                'name' => 'Natural Yogurt 500 g',
                'slug' => 'natural-yogurt-500-g',
                'description' => 'Live-culture, thick natural yogurt made with whole organic milk from Hunza pastures.',
                'price' => 190,
                'unit' => '500 g',
                'stock' => 50,
                'batch_no' => 'HNZ-YGT-01',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '500 g Cup', 'price' => 190, 'stock' => 35],
                    ['label' => '1 kg Bucket', 'price' => 360, 'stock' => 15],
                ],
            ],
            [
                'seller_id' => $sellerSkardu->id,
                'category_id' => $categories['yogurt']->id,
                'name' => 'Thick Balti Dahi',
                'slug' => 'thick-balti-dahi',
                'description' => 'Velvety, naturally sweet and mildly tart dahi prepared with mountain cow milk.',
                'price' => 210,
                'unit' => '500 g',
                'stock' => 30,
                'batch_no' => 'SKD-YGT-02',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '500 g', 'price' => 210, 'stock' => 20],
                    ['label' => '1 kg', 'price' => 400, 'stock' => 10],
                ],
            ],
            [
                'seller_id' => $sellerGhizer->id,
                'category_id' => $categories['yogurt']->id,
                'name' => 'Greek-Style Strained Yogurt',
                'slug' => 'greek-style-strained-yogurt',
                'description' => 'Extra-thick strained Greek style yogurt with double protein content and zero whey.',
                'price' => 260,
                'unit' => '400 g',
                'stock' => 20,
                'batch_no' => 'GHZ-YGT-03',
                'produced_on' => Carbon::today()->subDays(2),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '400 g', 'price' => 260, 'stock' => 14],
                    ['label' => '800 g', 'price' => 500, 'stock' => 6],
                ],
            ],
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['yogurt']->id,
                'name' => 'Probiotic Mountain Yogurt',
                'slug' => 'probiotic-mountain-yogurt',
                'description' => 'Enriched with 5 beneficial live cultures for gut health and immune support.',
                'price' => 230,
                'unit' => '500 g',
                'stock' => 25,
                'batch_no' => 'HNZ-YGT-04',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '500 g', 'price' => 230, 'stock' => 17],
                    ['label' => '1 kg', 'price' => 440, 'stock' => 8],
                ],
            ],

            // CHEESE
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['cheese']->id,
                'name' => 'Aged Cheese 300 g',
                'slug' => 'aged-cheese-300-g',
                'description' => 'Cave-aged artisan hard cheese cured for over 6 months in cool Hunza cellars.',
                'price' => 980,
                'unit' => '300 g',
                'stock' => 14,
                'batch_no' => 'HNZ-CHS-01',
                'produced_on' => Carbon::today()->subMonths(6),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '300 g Wedge', 'price' => 980, 'stock' => 9],
                    ['label' => '600 g Block', 'price' => 1900, 'stock' => 5],
                ],
            ],
            [
                'seller_id' => $sellerSkardu->id,
                'category_id' => $categories['cheese']->id,
                'name' => 'Balti Hard Yak Cheese',
                'slug' => 'balti-hard-yak-cheese',
                'description' => 'Authentic Balti dry yak cheese (Chhurpi style), rich in calcium and intense nutty flavor.',
                'price' => 1250,
                'unit' => '300 g',
                'stock' => 7, // low stock
                'batch_no' => 'SKD-CHS-02',
                'produced_on' => Carbon::today()->subMonths(4),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '300 g Pack', 'price' => 1250, 'stock' => 5],
                    ['label' => '600 g Pack', 'price' => 2400, 'stock' => 2],
                ],
            ],
            [
                'seller_id' => $sellerGhizer->id,
                'category_id' => $categories['cheese']->id,
                'name' => 'Alpine Gouda Style Cheese',
                'slug' => 'alpine-gouda-style-cheese',
                'description' => 'Mild, creamy and slightly sweet mountain Gouda crafted with whole raw pasture milk.',
                'price' => 1100,
                'unit' => '300 g',
                'stock' => 15,
                'batch_no' => 'GHZ-CHS-03',
                'produced_on' => Carbon::today()->subMonths(3),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '300 g', 'price' => 1100, 'stock' => 10],
                    ['label' => '500 g', 'price' => 1800, 'stock' => 5],
                ],
            ],
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['cheese']->id,
                'name' => 'Soft Mozzarella Ball',
                'slug' => 'soft-mozzarella-ball',
                'description' => 'Fresh handmade mozzarella ball stored in brine, super stretchy and milky.',
                'price' => 750,
                'unit' => '250 g',
                'stock' => 18,
                'batch_no' => 'HNZ-CHS-04',
                'produced_on' => Carbon::today()->subDays(2),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '250 g Ball', 'price' => 750, 'stock' => 12],
                    ['label' => '500 g Pack', 'price' => 1400, 'stock' => 6],
                ],
            ],

            // PANEER
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['paneer']->id,
                'name' => 'Fresh Paneer 400 g',
                'slug' => 'fresh-paneer-400-g',
                'description' => 'Non-melting, soft and juicy cottage cheese made from fresh whole organic milk.',
                'price' => 540,
                'unit' => '400 g',
                'stock' => 35,
                'batch_no' => 'HNZ-PNR-01',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '400 g Block', 'price' => 540, 'stock' => 20],
                    ['label' => '800 g Block', 'price' => 1050, 'stock' => 15],
                ],
            ],
            [
                'seller_id' => $sellerSkardu->id,
                'category_id' => $categories['paneer']->id,
                'name' => 'Mountain Herb Paneer',
                'slug' => 'mountain-herb-paneer',
                'description' => 'Fresh artisan paneer infused with local wild thyme and coriander seeds.',
                'price' => 590,
                'unit' => '400 g',
                'stock' => 20,
                'batch_no' => 'SKD-PNR-02',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '400 g', 'price' => 590, 'stock' => 14],
                    ['label' => '800 g', 'price' => 1150, 'stock' => 6],
                ],
            ],

            // CREAM
            [
                'seller_id' => $sellerHunza->id,
                'category_id' => $categories['cream']->id,
                'name' => 'Rich Malai Clotted Cream',
                'slug' => 'rich-malai-clotted-cream',
                'description' => 'Decadent, thick clotted layer of pure organic cream skimmed from simmered whole milk.',
                'price' => 420,
                'unit' => '250 g',
                'stock' => 25,
                'batch_no' => 'HNZ-CRM-01',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '250 g Jar', 'price' => 420, 'stock' => 18],
                    ['label' => '500 g Jar', 'price' => 800, 'stock' => 7],
                ],
            ],
            [
                'seller_id' => $sellerGhizer->id,
                'category_id' => $categories['cream']->id,
                'name' => 'Pure Heavy Whipping Cream',
                'slug' => 'pure-heavy-whipping-cream',
                'description' => 'High-fat 40% organic whipping cream without stabilizers, perfect for desserts and coffee.',
                'price' => 460,
                'unit' => '250 g',
                'stock' => 20,
                'batch_no' => 'GHZ-CRM-02',
                'produced_on' => Carbon::today()->subDays(2),
                'is_organic' => true,
                'status' => 'approved',
                'variants' => [
                    ['label' => '250 g', 'price' => 460, 'stock' => 12],
                    ['label' => '500 g', 'price' => 880, 'stock' => 8],
                ],
            ],

            // 2 PENDING PRODUCTS
            [
                'seller_id' => $sellerAstore->id,
                'category_id' => $categories['butter']->id,
                'name' => 'Astore Highland Raw Butter',
                'slug' => 'astore-highland-raw-butter',
                'description' => 'Raw unprocessed mountain butter submitted for organic certification.',
                'price' => 720,
                'unit' => '300 g',
                'stock' => 10,
                'batch_no' => 'AST-BTR-01',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'pending',
                'variants' => [
                    ['label' => '300 g', 'price' => 720, 'stock' => 7],
                    ['label' => '600 g', 'price' => 1400, 'stock' => 3],
                ],
            ],
            [
                'seller_id' => $sellerNagar->id,
                'category_id' => $categories['cheese']->id,
                'name' => 'Nagar Fresh Goat Cheese',
                'slug' => 'nagar-fresh-goat-cheese',
                'description' => 'Soft crumbly goat cheese with mild acidity submitted for catalog review.',
                'price' => 850,
                'unit' => '250 g',
                'stock' => 12,
                'batch_no' => 'NGR-CHS-01',
                'produced_on' => Carbon::today()->subDays(2),
                'is_organic' => true,
                'status' => 'pending',
                'variants' => [
                    ['label' => '250 g', 'price' => 850, 'stock' => 8],
                    ['label' => '500 g', 'price' => 1650, 'stock' => 4],
                ],
            ],
        ];

        $productImagesMap = [
            // MILK
            'fresh-cow-milk-1-litre' => 'products/fresh_cow_milk.jpg',
            'pure-mountain-yak-milk' => 'products/pure_yak_milk.jpg',
            'pasture-fresh-goat-milk' => 'products/pasture_goat_milk.jpg',
            'raw-jersey-cow-milk' => 'products/raw_jersey_milk.jpg',

            // BUTTER
            'desi-butter-250-g' => 'products/desi_butter.jpg',
            'cultured-mountain-butter' => 'products/salted_butter.jpg',
            'salted-herb-butter' => 'products/garlic_herb_butter.jpg',
            'unsalted-farm-butter' => 'products/unsalted_butter.jpg',
            'astore-highland-raw-butter' => 'products/astore_raw_butter.jpg',

            // GHEE
            'pure-desi-ghee-500-g' => 'products/pure_desi_ghee.jpg',
            'baltistan-yak-ghee' => 'products/yak_ghee.jpg',
            'traditional-churn-ghee' => 'products/ayurvedic_ghee.jpg',
            'golden-herb-fed-cow-ghee' => 'products/cow_ghee.jpg',

            // YOGURT
            'natural-yogurt-500-g' => 'products/natural_yogurt.jpg',
            'thick-balti-dahi' => 'products/balti_dahi.jpg',
            'greek-style-strained-yogurt' => 'products/greek_yogurt.jpg',
            'probiotic-mountain-yogurt' => 'products/mountain_kefir.jpg',

            // CHEESE
            'aged-cheese-300-g' => 'products/aged_gouda.jpg',
            'balti-hard-yak-cheese' => 'products/smoked_cheddar.jpg',
            'alpine-gouda-style-cheese' => 'products/goat_cheese.jpg',
            'soft-mozzarella-ball' => 'products/mountain_feta.jpg',
            'nagar-fresh-goat-cheese' => 'products/nagar_goat_cheese.jpg',

            // PANEER
            'fresh-paneer-400-g' => 'products/cottage_paneer.jpg',
            'mountain-herb-paneer' => 'products/herb_paneer.jpg',

            // CREAM
            'rich-malai-clotted-cream' => 'products/clotted_cream.jpg',
            'pure-heavy-whipping-cream' => 'products/whipping_cream.jpg',
        ];

        $categoryImageMap = [
            'milk' => 'products/fresh_cow_milk.jpg',
            'butter' => 'products/desi_butter.jpg',
            'ghee' => 'products/pure_desi_ghee.jpg',
            'yogurt' => 'products/natural_yogurt.jpg',
            'cheese' => 'products/aged_gouda.jpg',
            'paneer' => 'products/cottage_paneer.jpg',
            'cream' => 'products/clotted_cream.jpg',
        ];

        $createdProducts = [];
        foreach ($productsSeed as $pData) {
            $variants = $pData['variants'] ?? [];
            unset($pData['variants']);

            $product = Product::create($pData);
            $createdProducts[$product->slug] = $product;

            $catSlug = $product->category->slug ?? 'milk';
            $imagePath = $productImagesMap[$product->slug] ?? ($categoryImageMap[$catSlug] ?? 'products/fresh_cow_milk.jpg');
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $imagePath,
                'sort_order' => 1,
            ]);

            foreach ($variants as $v) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'label' => $v['label'],
                    'price' => $v['price'],
                    'stock' => $v['stock'],
                ]);
            }
        }

        // 8. Sample Orders (5 Orders in different statuses)
        // Order 1: Delivered (COD) - Buyer Ali, Seller Hunza
        $deliveryFee = 150;
        $p1 = $createdProducts['fresh-cow-milk-1-litre'];
        $p2 = $createdProducts['pure-desi-ghee-500-g'];
        $item1Qty = 2;
        $item1Price = $p1->price;
        $item1Total = $item1Qty * $item1Price; // 440

        $item2Qty = 1;
        $item2Price = $p2->price;
        $item2Total = $item2Qty * $item2Price; // 1850

        $subtotal1 = $item1Total + $item2Total; // 2290
        $total1 = $subtotal1 + $deliveryFee; // 2440
        $commission1 = (int) round($subtotal1 * 0.05); // 115

        $group1 = OrderGroup::create([
            'user_id' => $buyerAli->id,
            'group_no' => 'GRP-2026-0001',
            'subtotal' => $subtotal1,
            'delivery_total' => $deliveryFee,
            'discount' => 0,
            'grand_total' => $total1,
            'payment_method' => 'cod',
            'shipping_full_name' => $buyerAliAddress1->full_name,
            'shipping_phone' => $buyerAliAddress1->phone,
            'shipping_city' => $buyerAliAddress1->city,
            'shipping_area' => $buyerAliAddress1->area,
            'shipping_address_line' => $buyerAliAddress1->address_line,
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        $order1 = Order::create([
            'order_group_id' => $group1->id,
            'order_no' => 'OD-HNZ-1001',
            'buyer_id' => $buyerAli->id,
            'seller_id' => $sellerHunza->id,
            'subtotal' => $subtotal1,
            'delivery_fee' => $deliveryFee,
            'commission' => $commission1,
            'total' => $total1,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'rider_name' => 'Shamsuddin',
            'rider_phone' => '03419998877',
            'created_at' => Carbon::now()->subDays(4),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $p1->id,
            'variant_id' => $p1->variants->first()?->id,
            'product_name' => $p1->name,
            'unit_price' => $item1Price,
            'quantity' => $item1Qty,
            'line_total' => $item1Total,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $p2->id,
            'variant_id' => $p2->variants->first()?->id,
            'product_name' => $p2->name,
            'unit_price' => $item2Price,
            'quantity' => $item2Qty,
            'line_total' => $item2Total,
        ]);

        Payment::create([
            'order_group_id' => $group1->id,
            'method' => 'cod',
            'gateway_ref' => 'CASH-REC-1001',
            'last4' => null,
            'amount' => $total1,
            'status' => 'paid',
            'paid_at' => Carbon::now()->subDays(2),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order1->id,
            'status' => 'new',
            'note' => 'Order placed successfully by buyer',
            'changed_by' => $buyerAli->id,
            'created_at' => Carbon::now()->subDays(4),
        ]);
        OrderStatusHistory::create([
            'order_id' => $order1->id,
            'status' => 'packing',
            'note' => 'Seller verified fresh batch and packed insulated container',
            'changed_by' => $sellerHunza->id,
            'created_at' => Carbon::now()->subDays(3)->addHours(2),
        ]);
        OrderStatusHistory::create([
            'order_id' => $order1->id,
            'status' => 'in_transit',
            'note' => 'Handed over to rider Shamsuddin (03419998877)',
            'changed_by' => $sellerHunza->id,
            'created_at' => Carbon::now()->subDays(3)->addHours(6),
        ]);
        OrderStatusHistory::create([
            'order_id' => $order1->id,
            'status' => 'delivered',
            'note' => 'Delivered to buyer doorstep. Cash collected.',
            'changed_by' => $admin->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // Review for delivered product
        Review::create([
            'product_id' => $p2->id,
            'user_id' => $buyerAli->id,
            'order_id' => $order1->id,
            'rating' => 5,
            'comment' => 'The purest desi ghee I have had in Gilgit! Incredible aroma and golden texture.',
        ]);

        // Order 2: In Transit (Card Paid) - Buyer Ali, Seller Skardu
        $pYak = $createdProducts['pure-mountain-yak-milk'];
        $pYakGhee = $createdProducts['baltistan-yak-ghee'];
        $subtotal2 = ($pYak->price * 1) + ($pYakGhee->price * 1); // 350 + 2400 = 2750
        $total2 = $subtotal2 + $deliveryFee; // 2900
        $commission2 = (int) round($subtotal2 * 0.05);

        $group2 = OrderGroup::create([
            'user_id' => $buyerAli->id,
            'group_no' => 'GRP-2026-0002',
            'subtotal' => $subtotal2,
            'delivery_total' => $deliveryFee,
            'discount' => 0,
            'grand_total' => $total2,
            'payment_method' => 'card',
            'shipping_full_name' => $buyerAliAddress1->full_name,
            'shipping_phone' => $buyerAliAddress1->phone,
            'shipping_city' => $buyerAliAddress1->city,
            'shipping_area' => $buyerAliAddress1->area,
            'shipping_address_line' => $buyerAliAddress1->address_line,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        $order2 = Order::create([
            'order_group_id' => $group2->id,
            'order_no' => 'OD-SKD-1002',
            'buyer_id' => $buyerAli->id,
            'seller_id' => $sellerSkardu->id,
            'subtotal' => $subtotal2,
            'delivery_fee' => $deliveryFee,
            'commission' => $commission2,
            'total' => $total2,
            'status' => 'in_transit',
            'payment_status' => 'paid',
            'rider_name' => 'Karim Baig',
            'rider_phone' => '03412345678',
            'created_at' => Carbon::now()->subDays(1),
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $pYak->id,
            'variant_id' => $pYak->variants->first()?->id,
            'product_name' => $pYak->name,
            'unit_price' => $pYak->price,
            'quantity' => 1,
            'line_total' => $pYak->price,
        ]);
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $pYakGhee->id,
            'variant_id' => $pYakGhee->variants->first()?->id,
            'product_name' => $pYakGhee->name,
            'unit_price' => $pYakGhee->price,
            'quantity' => 1,
            'line_total' => $pYakGhee->price,
        ]);

        Payment::create([
            'order_group_id' => $group2->id,
            'method' => 'card',
            'gateway_ref' => 'PAY-CARD-883921',
            'last4' => '4242',
            'amount' => $total2,
            'status' => 'paid',
            'paid_at' => Carbon::now()->subDays(1),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order2->id,
            'status' => 'new',
            'note' => 'Payment received online via Card',
            'changed_by' => $buyerAli->id,
            'created_at' => Carbon::now()->subDays(1),
        ]);
        OrderStatusHistory::create([
            'order_id' => $order2->id,
            'status' => 'in_transit',
            'note' => 'Dispatched from Skardu via refrigerated mountain transit rider Karim Baig',
            'changed_by' => $sellerSkardu->id,
            'created_at' => Carbon::now()->subHours(6),
        ]);

        // Order 3: Packing (Wallet) - Buyer Fatima, Seller Ghizer
        $pGhizerCheese = $createdProducts['alpine-gouda-style-cheese'];
        $subtotal3 = $pGhizerCheese->price * 2; // 2200
        $total3 = $subtotal3 + $deliveryFee; // 2350
        $commission3 = (int) round($subtotal3 * 0.05);

        $group3 = OrderGroup::create([
            'user_id' => $buyerFatima->id,
            'group_no' => 'GRP-2026-0003',
            'subtotal' => $subtotal3,
            'delivery_total' => $deliveryFee,
            'discount' => 0,
            'grand_total' => $total3,
            'payment_method' => 'wallet',
            'shipping_full_name' => $buyerFatimaAddress->full_name,
            'shipping_phone' => $buyerFatimaAddress->phone,
            'shipping_city' => $buyerFatimaAddress->city,
            'shipping_area' => $buyerFatimaAddress->area,
            'shipping_address_line' => $buyerFatimaAddress->address_line,
            'created_at' => Carbon::now()->subHours(8),
        ]);

        $order3 = Order::create([
            'order_group_id' => $group3->id,
            'order_no' => 'OD-GHZ-1003',
            'buyer_id' => $buyerFatima->id,
            'seller_id' => $sellerGhizer->id,
            'subtotal' => $subtotal3,
            'delivery_fee' => $deliveryFee,
            'commission' => $commission3,
            'total' => $total3,
            'status' => 'packing',
            'payment_status' => 'paid',
            'created_at' => Carbon::now()->subHours(8),
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'product_id' => $pGhizerCheese->id,
            'variant_id' => $pGhizerCheese->variants->first()?->id,
            'product_name' => $pGhizerCheese->name,
            'unit_price' => $pGhizerCheese->price,
            'quantity' => 2,
            'line_total' => $subtotal3,
        ]);

        Payment::create([
            'order_group_id' => $group3->id,
            'method' => 'wallet',
            'gateway_ref' => 'WLT-TXN-44910',
            'last4' => null,
            'amount' => $total3,
            'status' => 'paid',
            'paid_at' => Carbon::now()->subHours(8),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order3->id,
            'status' => 'new',
            'note' => 'Order placed with digital wallet payment',
            'changed_by' => $buyerFatima->id,
            'created_at' => Carbon::now()->subHours(8),
        ]);
        OrderStatusHistory::create([
            'order_id' => $order3->id,
            'status' => 'packing',
            'note' => 'Ghizer farm is packing freshly sliced Gouda cheese in vacuum wrap',
            'changed_by' => $sellerGhizer->id,
            'created_at' => Carbon::now()->subHours(4),
        ]);

        // Order 4: New (COD Unpaid) - Buyer Fatima, Seller Hunza
        $pPaneer = $createdProducts['fresh-paneer-400-g'];
        $subtotal4 = $pPaneer->price * 1; // 540
        $total4 = $subtotal4 + $deliveryFee; // 690
        $commission4 = (int) round($subtotal4 * 0.05);

        $group4 = OrderGroup::create([
            'user_id' => $buyerFatima->id,
            'group_no' => 'GRP-2026-0004',
            'subtotal' => $subtotal4,
            'delivery_total' => $deliveryFee,
            'discount' => 0,
            'grand_total' => $total4,
            'payment_method' => 'cod',
            'shipping_full_name' => $buyerFatimaAddress->full_name,
            'shipping_phone' => $buyerFatimaAddress->phone,
            'shipping_city' => $buyerFatimaAddress->city,
            'shipping_area' => $buyerFatimaAddress->area,
            'shipping_address_line' => $buyerFatimaAddress->address_line,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        $order4 = Order::create([
            'order_group_id' => $group4->id,
            'order_no' => 'OD-HNZ-1004',
            'buyer_id' => $buyerFatima->id,
            'seller_id' => $sellerHunza->id,
            'subtotal' => $subtotal4,
            'delivery_fee' => $deliveryFee,
            'commission' => $commission4,
            'total' => $total4,
            'status' => 'new',
            'payment_status' => 'unpaid',
            'created_at' => Carbon::now()->subHours(2),
        ]);

        OrderItem::create([
            'order_id' => $order4->id,
            'product_id' => $pPaneer->id,
            'variant_id' => $pPaneer->variants->first()?->id,
            'product_name' => $pPaneer->name,
            'unit_price' => $pPaneer->price,
            'quantity' => 1,
            'line_total' => $subtotal4,
        ]);

        Payment::create([
            'order_group_id' => $group4->id,
            'method' => 'cod',
            'gateway_ref' => null,
            'last4' => null,
            'amount' => $total4,
            'status' => 'pending',
            'paid_at' => null,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order4->id,
            'status' => 'new',
            'note' => 'New COD order placed awaiting seller confirmation',
            'changed_by' => $buyerFatima->id,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        // Order 5: Cancelled (COD Unpaid) - Buyer Ali, Seller Skardu
        $pButter = $createdProducts['cultured-mountain-butter'];
        $subtotal5 = $pButter->price * 1; // 700
        $total5 = $subtotal5 + $deliveryFee; // 850
        $commission5 = (int) round($subtotal5 * 0.05);

        $group5 = OrderGroup::create([
            'user_id' => $buyerAli->id,
            'group_no' => 'GRP-2026-0005',
            'subtotal' => $subtotal5,
            'delivery_total' => $deliveryFee,
            'discount' => 0,
            'grand_total' => $total5,
            'payment_method' => 'cod',
            'shipping_full_name' => $buyerAliAddress1->full_name,
            'shipping_phone' => $buyerAliAddress1->phone,
            'shipping_city' => $buyerAliAddress1->city,
            'shipping_area' => $buyerAliAddress1->area,
            'shipping_address_line' => $buyerAliAddress1->address_line,
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(5),
        ]);

        $order5 = Order::create([
            'order_group_id' => $group5->id,
            'order_no' => 'OD-SKD-1005',
            'buyer_id' => $buyerAli->id,
            'seller_id' => $sellerSkardu->id,
            'subtotal' => $subtotal5,
            'delivery_fee' => $deliveryFee,
            'commission' => $commission5,
            'total' => $total5,
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'cancel_reason' => 'Buyer requested cancellation due to change of delivery date and address.',
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(5),
        ]);

        OrderItem::create([
            'order_id' => $order5->id,
            'product_id' => $pButter->id,
            'variant_id' => $pButter->variants->first()?->id,
            'product_name' => $pButter->name,
            'unit_price' => $pButter->price,
            'quantity' => 1,
            'line_total' => $subtotal5,
        ]);

        Payment::create([
            'order_group_id' => $group5->id,
            'method' => 'cod',
            'gateway_ref' => null,
            'last4' => null,
            'amount' => $total5,
            'status' => 'failed',
            'paid_at' => null,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order5->id,
            'status' => 'new',
            'note' => 'Order created',
            'changed_by' => $buyerAli->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        OrderStatusHistory::create([
            'order_id' => $order5->id,
            'status' => 'cancelled',
            'note' => 'Buyer requested cancellation due to change of delivery date and address.',
            'changed_by' => $buyerAli->id,
            'created_at' => Carbon::now()->subDays(5)->addHours(1),
        ]);

        // 9. Sample Deals (3 Days, 1 Week, 1 Month)
        // Deal 1: 1 Week Routine Deal (Ali Khan with Hunza Valley Farm)
        $pMilk = $createdProducts['fresh-cow-milk-1-litre'];
        $deal1 = Deal::create([
            'deal_no' => 'DEAL-2026-0001',
            'buyer_id' => $buyerAli->id,
            'seller_id' => $sellerHunza->id,
            'product_id' => $pMilk->id,
            'product_variant_id' => null,
            'address_id' => $buyerAliAddress1->id,
            'title' => '1 Week Routine Deal - ' . $pMilk->name,
            'duration_type' => '1_week',
            'total_days' => 7,
            'delivered_days' => 3,
            'quantity_per_day' => 2,
            'unit_price' => $pMilk->price,
            'discount_percent' => 10,
            'total_amount' => (int) round(2 * $pMilk->price * 7 * 0.90),
            'delivery_slot' => 'morning',
            'start_date' => Carbon::today()->subDays(3)->toDateString(),
            'end_date' => Carbon::today()->addDays(3)->toDateString(),
            'status' => 'active',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'special_instructions' => 'Drop milk in the gate side container at 6:30 AM.',
        ]);

        for ($d = 1; $d <= 7; $d++) {
            $schedDate = Carbon::today()->subDays(3)->addDays($d - 1)->toDateString();
            $isDelivered = ($d <= 3);
            DealDelivery::create([
                'deal_id' => $deal1->id,
                'day_number' => $d,
                'scheduled_date' => $schedDate,
                'status' => $isDelivered ? 'delivered' : 'scheduled',
                'delivered_at' => $isDelivered ? Carbon::parse($schedDate)->setHour(7)->setMinute(15) : null,
                'notes' => $isDelivered ? 'Morning batch delivered cold' : null,
            ]);
        }

        // Deal 2: 1 Month Family Supply Deal (Ali Khan with Skardu Farmers Co-op)
        $pYak = $createdProducts['pure-mountain-yak-milk'];
        $deal2 = Deal::create([
            'deal_no' => 'DEAL-2026-0002',
            'buyer_id' => $buyerAli->id,
            'seller_id' => $sellerSkardu->id,
            'product_id' => $pYak->id,
            'product_variant_id' => null,
            'address_id' => $buyerAliAddress1->id,
            'title' => '1 Month Family Supply Deal - ' . $pYak->name,
            'duration_type' => '1_month',
            'total_days' => 30,
            'delivered_days' => 10,
            'quantity_per_day' => 1,
            'unit_price' => $pYak->price,
            'discount_percent' => 15,
            'total_amount' => (int) round($pYak->price * 30 * 0.85),
            'delivery_slot' => 'morning',
            'start_date' => Carbon::today()->subDays(10)->toDateString(),
            'end_date' => Carbon::today()->addDays(19)->toDateString(),
            'status' => 'active',
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'special_instructions' => 'Family monthly subscription. Leave outside door.',
        ]);

        for ($d = 1; $d <= 30; $d++) {
            $schedDate = Carbon::today()->subDays(10)->addDays($d - 1)->toDateString();
            $isDelivered = ($d <= 10);
            DealDelivery::create([
                'deal_id' => $deal2->id,
                'day_number' => $d,
                'scheduled_date' => $schedDate,
                'status' => $isDelivered ? 'delivered' : 'scheduled',
                'delivered_at' => $isDelivered ? Carbon::parse($schedDate)->setHour(7)->setMinute(30) : null,
                'notes' => $isDelivered ? 'Delivered by Skardu cold-chain rider' : null,
            ]);
        }

        // Deal 3: 3 Days Trial Deal (Fatima Zahra with Hunza Valley Farm)
        $pYogurt = $createdProducts['natural-yogurt-500-g'];
        $deal3 = Deal::create([
            'deal_no' => 'DEAL-2026-0003',
            'buyer_id' => $buyerFatima->id,
            'seller_id' => $sellerHunza->id,
            'product_id' => $pYogurt->id,
            'product_variant_id' => null,
            'address_id' => $buyerFatimaAddress->id,
            'title' => '3 Days Trial Deal - ' . $pYogurt->name,
            'duration_type' => '3_days',
            'total_days' => 3,
            'delivered_days' => 3,
            'quantity_per_day' => 1,
            'unit_price' => $pYogurt->price,
            'discount_percent' => 5,
            'total_amount' => (int) round($pYogurt->price * 3 * 0.95),
            'delivery_slot' => 'morning',
            'start_date' => Carbon::today()->subDays(4)->toDateString(),
            'end_date' => Carbon::today()->subDays(2)->toDateString(),
            'status' => 'completed',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'special_instructions' => 'Trial pack for breakfast taste test.',
        ]);

        for ($d = 1; $d <= 3; $d++) {
            $schedDate = Carbon::today()->subDays(4)->addDays($d - 1)->toDateString();
            DealDelivery::create([
                'deal_id' => $deal3->id,
                'day_number' => $d,
                'scheduled_date' => $schedDate,
                'status' => 'delivered',
                'delivered_at' => Carbon::parse($schedDate)->setHour(7)->setMinute(45),
                'notes' => 'Trial pack delivered',
            ]);
        }
    }
}
