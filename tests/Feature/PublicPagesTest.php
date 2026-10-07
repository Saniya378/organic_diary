<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_home_page_loads_successfully_with_required_sections(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Pure, Grass-Fed Mountain Dairy Directly from Local Herders');
        $response->assertSee('Shop Fresh Batches');
        $response->assertSee('Meet Our Farmers');
        $response->assertSee('100% Organic Pastures');
        $response->assertSee('Browse by Dairy Type');
        $response->assertSee('Fresh Today');
        $response->assertSee('How Mountain Dairy Reaches You');
        $response->assertSee('Are you a dairy farmer in Gilgit-Baltistan?');
    }

    public function test_shop_page_filters_only_approved_products_from_approved_sellers(): void
    {
        $response = $this->get('/shop');
        $response->assertStatus(200);
        $response->assertSee('Filters');
        $response->assertSee('Fresh Cow Milk 1 Litre');

        // Verify pending products are NOT visible in the shop catalog
        $response->assertDontSee('Astore Highland Raw Butter');
        $response->assertDontSee('Nagar Fresh Goat Cheese');
    }

    public function test_shop_page_filtering_by_category_and_search(): void
    {
        $response = $this->get('/shop?category=ghee');
        $response->assertStatus(200);
        $response->assertSee('Pure Desi Ghee 500 g');

        $searchResponse = $this->get('/shop?q=Ghee');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Pure Desi Ghee 500 g');
    }

    public function test_product_detail_page_loads_with_variants_and_seller_card(): void
    {
        $product = Product::approved()->first();
        $this->assertNotNull($product);

        $response = $this->get('/product/' . $product->slug);
        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->seller->sellerProfile->farm_name);
        $response->assertSee('Batch Number:');
        $response->assertSee('Add to Cart');
        $response->assertSee('Buy Now');
        $response->assertSee('Customer Reviews');
    }

    public function test_farmers_page_and_single_farm_page_load(): void
    {
        $response = $this->get('/farmers');
        $response->assertStatus(200);
        $response->assertSee('Hunza Valley Farm');
        $response->assertSee('Skardu Farmers Co-op');
        $response->assertSee('Ghizer Organic Dairy');

        // Pending sellers should not be in approved farmers directory
        $response->assertDontSee('Astore Valley Farm');
        $response->assertDontSee('Nagar Mountain Dairy');

        $approvedFarmer = SellerProfile::approved()->first();
        $this->assertNotNull($approvedFarmer);

        $farmResponse = $this->get('/farmers/' . $approvedFarmer->id);
        $farmResponse->assertStatus(200);
        $farmResponse->assertSee($approvedFarmer->farm_name);
        $farmResponse->assertSee($approvedFarmer->location);
    }
}
