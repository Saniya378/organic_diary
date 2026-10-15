<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\SellerProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TraditionalProductsSeeder extends Seeder
{
    public function run(): void
    {
        $tradCat = Category::firstOrCreate(['slug' => 'traditional-specialties'], ['name' => 'Traditional Specialties', 'is_active' => true]);
        $cheeseCat = Category::firstOrCreate(['slug' => 'cheese'], ['name' => 'Cheese', 'is_active' => true]);
        $butterCat = Category::firstOrCreate(['slug' => 'butter'], ['name' => 'Butter', 'is_active' => true]);
        $creamCat = Category::firstOrCreate(['slug' => 'cream'], ['name' => 'Cream', 'is_active' => true]);
        $milkCat = Category::firstOrCreate(['slug' => 'milk'], ['name' => 'Milk', 'is_active' => true]);

        $skardu = SellerProfile::where('farm_name', 'like', '%Skardu%')->first()?->user_id ?? 2;
        $hunza = SellerProfile::where('farm_name', 'like', '%Hunza%')->first()?->user_id ?? 1;
        $ghizer = SellerProfile::where('farm_name', 'like', '%Ghizer%')->first()?->user_id ?? 3;

        $items = [
            [
                'seller_id' => $skardu,
                'category_id' => $cheeseCat->id,
                'name' => 'Qurut (Salty Dried Cheese)',
                'slug' => 'qurut-salty-dried-cheese',
                'description' => 'Hard, dense, and highly salted cheese made from strained cow or goat milk yogurt. Shaped into small balls or chunks and sun-dried until hardened. A staple high-energy winter resource across Gilgit-Baltistan.',
                'price' => 650,
                'unit' => '250 g',
                'stock' => 25,
                'batch_no' => 'SKD-QRT-01',
                'produced_on' => Carbon::today()->subMonths(1),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/qurut.jpg',
                'variants' => [
                    ['label' => '250 g Pouch', 'price' => 650, 'stock' => 15],
                    ['label' => '500 g Family Pack', 'price' => 1250, 'stock' => 10],
                ]
            ],
            [
                'seller_id' => $hunza,
                'category_id' => $cheeseCat->id,
                'name' => 'Chamus / Chhurpi (Local Soft Cheese)',
                'slug' => 'chamus-chhurpi-local-soft-cheese',
                'description' => 'Fresh, unripened curd cheese similar to cottage cheese. Made by curdling buttermilk or skimmed cow/goat milk, pressing out the whey. Eaten raw or added to mountain soups.',
                'price' => 550,
                'unit' => '350 g',
                'stock' => 20,
                'batch_no' => 'HNZ-CHM-01',
                'produced_on' => Carbon::today()->subDays(1),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/chamus_chhurpi.jpg',
                'variants' => [
                    ['label' => '350 g Block', 'price' => 550, 'stock' => 12],
                    ['label' => '700 g Block', 'price' => 1050, 'stock' => 8],
                ]
            ],
            [
                'seller_id' => $hunza,
                'category_id' => $butterCat->id,
                'name' => 'Makhan (Traditional Churned Butter)',
                'slug' => 'makhan-traditional-churned-butter',
                'description' => 'Fresh cow and goat milk manually churned in tall wooden bilona vessels to produce aromatic Makhan. Unprocessed and 100% organic.',
                'price' => 750,
                'unit' => '300 g',
                'stock' => 30,
                'batch_no' => 'HNZ-MKH-01',
                'produced_on' => Carbon::today()->subDays(2),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/makhan_butter.jpg',
                'variants' => [
                    ['label' => '300 g', 'price' => 750, 'stock' => 20],
                    ['label' => '600 g', 'price' => 1450, 'stock' => 10],
                ]
            ],
            [
                'seller_id' => $skardu,
                'category_id' => $tradCat->id,
                'name' => 'Gurgur Cha / Shir Chai (Salted Butter Tea)',
                'slug' => 'gurgur-cha-shir-chai',
                'description' => 'Signature Himalayan beverage across Gilgit-Baltistan. Blends mountain green or black tea leaves with fresh milk, salt, and generous amounts of churned butter. Served hot.',
                'price' => 350,
                'unit' => '1 Flask (750ml)',
                'stock' => 25,
                'batch_no' => 'SKD-GRG-01',
                'produced_on' => Carbon::today(),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/gurgur_cha.jpg',
                'variants' => [
                    ['label' => '750 ml Flask', 'price' => 350, 'stock' => 15],
                    ['label' => '1.5 Litre Family Pot', 'price' => 650, 'stock' => 10],
                ]
            ],
            [
                'seller_id' => $hunza,
                'category_id' => $tradCat->id,
                'name' => 'Buroshapik / Ghilmindi (Cheese-Stuffed Flatbread)',
                'slug' => 'buroshapik-ghilmindi',
                'description' => 'Traditional Hunza delicacy of thin, pan-fried wheat chapatis layered with fresh local cheese curd, yogurt, mint, coriander, onions, ground walnuts, and drizzled with apricot oil.',
                'price' => 600,
                'unit' => 'Plate (2 Layers)',
                'stock' => 18,
                'batch_no' => 'HNZ-BUR-01',
                'produced_on' => Carbon::today(),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/buroshapik.jpg',
                'variants' => [
                    ['label' => 'Standard Plate', 'price' => 600, 'stock' => 12],
                    ['label' => 'Double Layer Plate', 'price' => 1100, 'stock' => 6],
                ]
            ],
            [
                'seller_id' => $ghizer,
                'category_id' => $tradCat->id,
                'name' => 'Molida (Crumbled Bread in Buttery Milk)',
                'slug' => 'molida-crumbled-bread-in-buttery-milk',
                'description' => 'Celebratory regional dish made by breaking freshly baked wheat flatbread into tiny pieces, soaked thoroughly in a hot mixture of sweetened milk, churned butter, and native apricot oil.',
                'price' => 500,
                'unit' => '1 Bowl (450g)',
                'stock' => 15,
                'batch_no' => 'GHZ-MLD-01',
                'produced_on' => Carbon::today(),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/molida.jpg',
                'variants' => [
                    ['label' => '1 Bowl (450g)', 'price' => 500, 'stock' => 15],
                ]
            ],
            [
                'seller_id' => $ghizer,
                'category_id' => $milkCat->id,
                'name' => 'Lassi / Machhba (Traditional Buttermilk)',
                'slug' => 'lassi-machhba-traditional-buttermilk',
                'description' => 'Natural byproduct of churning butter from cultured goat or cow milk. Valued for its cooling probiotic properties and crisp traditional taste.',
                'price' => 180,
                'unit' => '1 Litre',
                'stock' => 40,
                'batch_no' => 'GHZ-LAS-01',
                'produced_on' => Carbon::today(),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/lassi_machhba.jpg',
                'variants' => [
                    ['label' => '1 Litre Bottle', 'price' => 180, 'stock' => 25],
                    ['label' => '2 Litre Jug', 'price' => 340, 'stock' => 15],
                ]
            ],
            [
                'seller_id' => $hunza,
                'category_id' => $tradCat->id,
                'name' => 'Shirbrinj (Traditional Milk Rice Pudding)',
                'slug' => 'shirbrinj-traditional-milk-rice',
                'description' => 'Regional slow-cooked rice pudding made by boiling local grains directly in rich whole milk, delicately spiced and topped with melted desi ghee and crushed Himalayan walnuts.',
                'price' => 450,
                'unit' => '1 Bowl (400g)',
                'stock' => 20,
                'batch_no' => 'HNZ-SHR-01',
                'produced_on' => Carbon::today(),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/shirbrinj.jpg',
                'variants' => [
                    ['label' => '1 Bowl (400g)', 'price' => 450, 'stock' => 14],
                    ['label' => '2 Bowls Family Pack', 'price' => 850, 'stock' => 6],
                ]
            ],
            [
                'seller_id' => $skardu,
                'category_id' => $milkCat->id,
                'name' => 'Shirrovoghan (Boiled Milk & Butter Energy Drink)',
                'slug' => 'shirrovoghan-boiled-milk-and-butter',
                'description' => 'Nutritious high-altitude winter energy food combining whole fresh milk with heavy cream and rich butter fat, boiled down into a hearty warming drink and bread dip.',
                'price' => 380,
                'unit' => '500 ml',
                'stock' => 22,
                'batch_no' => 'SKD-SHV-01',
                'produced_on' => Carbon::today(),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/shirrovoghan.jpg',
                'variants' => [
                    ['label' => '500 ml Flask', 'price' => 380, 'stock' => 15],
                    ['label' => '1 Litre Pot', 'price' => 720, 'stock' => 7],
                ]
            ],
            [
                'seller_id' => $hunza,
                'category_id' => $creamCat->id,
                'name' => 'Malai (Fresh Mountain Milk Cream / Skin)',
                'slug' => 'malai-fresh-milk-cream-skin',
                'description' => 'Thick, creamy fat layer skimmed off simmering whole milk over wood-fire stoves. Fresh and unpasteurized, paired traditionally with local Diram Fiti or Khurba breads.',
                'price' => 480,
                'unit' => '300 g',
                'stock' => 25,
                'batch_no' => 'HNZ-MAL-01',
                'produced_on' => Carbon::today(),
                'is_organic' => true,
                'status' => 'approved',
                'image' => 'products/malai.jpg',
                'variants' => [
                    ['label' => '300 g Jar', 'price' => 480, 'stock' => 18],
                    ['label' => '600 g Jar', 'price' => 920, 'stock' => 7],
                ]
            ]
        ];

        foreach ($items as $itm) {
            $variants = $itm['variants'];
            $image = $itm['image'];
            unset($itm['variants'], $itm['image']);

            $product = Product::updateOrCreate(['slug' => $itm['slug']], $itm);

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'sort_order' => 1],
                ['path' => $image]
            );

            foreach ($variants as $v) {
                ProductVariant::updateOrCreate(
                    ['product_id' => $product->id, 'label' => $v['label']],
                    ['price' => $v['price'], 'stock' => $v['stock']]
                );
            }
        }
    }
}
