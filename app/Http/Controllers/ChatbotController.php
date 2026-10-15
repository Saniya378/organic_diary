<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Process an incoming customer support chatbot query in Roman Urdu or English.
     */
    public function message(Request $request): JsonResponse
    {
        $message = trim((string) ($request->input('message') ?? $request->json('message') ?? ''));
        if ($message === '') {
            $raw = json_decode($request->getContent(), true);
            if (is_array($raw) && isset($raw['message'])) {
                $message = trim((string) $raw['message']);
            }
        }
        $context = $request->input('context', []);

        $isUrdu = $this->isRomanUrdu($message);

        if ($message === '') {
            $reply = $isUrdu
                ? "Assalam-o-Alaikum! 👋 Main **Ayla** hoon, Organic Dairy GB ki customer support assistant. Main aapki taza pahaari doodh, order tracking, ya daily milk deals me kya madad kar sakti hoon?"
                : "Hello! 👋 I am **Ayla**, your Organic Dairy Customer Support Assistant. How can I assist you with fresh mountain dairy products, order tracking, or subscriptions today?";

            return response()->json([
                'reply' => $reply,
                'type' => 'text',
                'suggestions' => [
                    '📦 Track My Order / Order Track',
                    '🥛 Fresh Milk & Ghee / Taza Doodh',
                    '🚚 Delivery Areas / Delivery ki Jagah',
                    '🎁 Milk Subscriptions / Doodh Deals',
                    '🧑‍🌾 Become a Seller / Farmer Banein',
                ],
            ]);
        }

        $lowerMsg = strtolower($message);

        // 1. Check for specific Order Tracking Number
        if (preg_match('/(ORD-[A-Z0-9-]+|#\s*[0-9]+|[0-9]{4,})/i', $message, $matches)) {
            $extracted = trim(str_replace(['#', ' '], '', $matches[1]));
            return $this->handleOrderLookup($extracted, $isUrdu);
        }

        // 2. Farm or Location availability inquiry (e.g. "is there any farm in oshikhandass", "hunza me farm hai?")
        if (
            preg_match('/\b(farm|farms|farmer|farmers|kisan|kisaan)\b/i', $lowerMsg) ||
            Str::contains($lowerMsg, ['available in', 'me farm', 'mai farm', 'mein farm', 'koi farm', 'farm available', 'farm hai', 'farms hain'])
        ) {
            // If user is asking how to become a seller / register as farmer
            if (Str::contains($lowerMsg, ['register', 'join', 'bechna', 'bech', 'banoo', 'banna', 'become', 'kaise banein', 'how to become', 'kese bane'])) {
                return $this->handleSellerRegistrationInquiry($isUrdu);
            }

            return $this->handleFarmLocationInquiry($message, $lowerMsg, $isUrdu);
        }

        // 3. Order tracking general query
        if (
            Str::contains($lowerMsg, ['track', 'order', 'status', 'kahan', 'kidhar', 'parcel', 'package', 'shipment', 'tracking', 'dispatch', 'pohancha', 'kab aye'])
            && !Str::contains($lowerMsg, ['how to order', 'order kaise', 'order karna', 'order krna'])
        ) {
            $reply = $isUrdu
                ? "📦 **Apna Dairy Order Track Karein**\n\nBaraye meharbani apna **Order Number** (misal ke tor par: `ORD-ABC1234`) yahan likhein ya neechay دیے gaye live tracker button par click karein."
                : "📦 **Track Your Dairy Order**\n\nPlease enter your **Order Number** (for example: `ORD-ABC1234`) or click below to open our visual live tracking portal.";

            return response()->json([
                'reply' => $reply,
                'type' => 'order_prompt',
                'suggestions' => [
                    $isUrdu ? '📦 Order ID likhein' : '📦 Enter Order ID',
                    $isUrdu ? '🥛 Taza Products Dekhein' : '🥛 View Products',
                    $isUrdu ? '🚚 Delivery Maloomat' : '🚚 Delivery Info',
                ],
                'actions' => [
                    [
                        'label' => $isUrdu ? 'Live Tracker Kholein' : 'Open Live Tracker Page',
                        'url' => route('orders.track'),
                        'icon' => 'bi-geo-alt-fill',
                    ],
                ],
            ]);
        }

        // 4. How to Order / Ordering process
        if (Str::contains($lowerMsg, ['how to order', 'order kaise', 'kaise mangwaye', 'kese mangwaye', 'order krna', 'order karna', 'buy', 'purchase', 'kharidna', 'kharid'])) {
            $reply = $isUrdu
                ? "🛒 **Organic Dairy GB par Order Karne ka Tareeqa**:\n\n1. **Shop** par jayein aur Gaye ka Doodh, Yak Butter, Desi Ghee ya Cheese pasand karein.\n2. **Add to Cart** karein aur Checkout par click karein.\n3. Apna delivery address aur phone number darj karein.\n4. **Cash on Delivery (COD)** ya **Online Bank/JazzCash** select karein.\n5. Taza farm harvest insulated cold boxes me aapke ghar pohanch jayegi!"
                : "🛒 **How to Order on Organic Dairy GB**:\n\n1. Browse our **Shop** for pasture-fed Cow milk, Yak butter, Desi ghee, or Mountain cheese.\n2. Add items to your cart and proceed to **Checkout**.\n3. Enter your delivery address in Gilgit-Baltistan.\n4. Select **Cash on Delivery (COD)** or **Bank Transfer / Digital Wallet**.\n5. Your order is fresh-harvested and delivered in insulated cold packaging!";

            return response()->json([
                'reply' => $reply,
                'type' => 'text',
                'suggestions' => [
                    $isUrdu ? '🥛 Products Dekhein' : '🥛 Browse Products',
                    $isUrdu ? '🎁 Doodh ki Subscriptions' : '🎁 Milk Subscriptions',
                    $isUrdu ? '🚚 Delivery Areas' : '🚚 Delivery Areas',
                ],
                'actions' => [
                    [
                        'label' => $isUrdu ? 'Shop par Jayein' : 'Go to Shop',
                        'url' => route('shop.index'),
                        'icon' => 'bi-bag-check-fill',
                    ],
                ],
            ]);
        }

        // 5. Products, Prices, and Specific Dairy Items
        $dairyKeywords = [
            'milk', 'doodh', 'butter', 'makhan', 'ghee', 'desi ghee', 'yak', 'cheese', 'paneer',
            'yogurt', 'dahi', 'honey', 'shahad', 'cream', 'malai', 'product', 'items', 'catalog', 'rate', 'price', 'khoya', 'keemat', 'kitne ka'
        ];

        if (Str::contains($lowerMsg, $dairyKeywords)) {
            return $this->handleProductInquiry($lowerMsg, $isUrdu);
        }

        // 6. Subscription Deals / Recurring milk
        if (Str::contains($lowerMsg, ['deal', 'deals', 'subscription', 'sub', '3-day', 'weekly', 'monthly', 'rozana', 'daily milk', 'nagha', 'recurring', 'haftawar', 'mahina', 'roz'])) {
            $reply = $isUrdu
                ? "🎁 **Taza Pahaari Doodh Subscriptions (Deals)**\n\nHar subah baghair nagha taza pahaari doodh aur dairy ghar mangwayein:\n- 🌿 **3-Day Trial:** Pahaari zaike ko test karne ke liye behtareen.\n- 🧈 **1-Week Essential:** Rozana subah taza doodh aur table makhhan.\n- 🏆 **1-Month Household:** Poore maheenay ki bachat (10% discount ke sath).\n\n*Aap kisi bhi waqt apne dashboard se delivery pause ya change kar sakte hain.*"
                : "🎁 **Fresh Mountain Dairy Subscriptions & Deals**\n\nEnjoy fresh morning doorstep deliveries:\n- 🌿 **3-Day Trial:** Great for testing pure mountain taste.\n- 🧈 **1-Week Essential:** Fresh daily milk & table butter.\n- 🏆 **1-Month Household:** Save up to 10% on monthly household dairy supplies.\n\n*You can pause, resume, or skip drops anytime from your dashboard.*";

            return response()->json([
                'reply' => $reply,
                'type' => 'text',
                'suggestions' => [
                    $isUrdu ? '🥛 Sub Deals Dekhein' : '🥛 View All Deals',
                    $isUrdu ? '🚚 Delivery Timings' : '🚚 Delivery Timings',
                    $isUrdu ? '💳 Payment Options' : '💳 Payment Options',
                ],
                'actions' => [
                    [
                        'label' => $isUrdu ? 'Subscription Deals Dekhein' : 'Explore Subscription Deals',
                        'url' => route('deals.index'),
                        'icon' => 'bi-calendar2-heart-fill',
                    ],
                ],
            ]);
        }

        // 7. Delivery, Shipping Coverage & Rates
        if (Str::contains($lowerMsg, ['delivery', 'ship', 'shipping', 'city', 'cities', 'area', 'areas', 'skardu', 'gilgit', 'hunza', 'nagar', 'ghizer', 'astore', 'charges', 'flat rate', 'timing', 'shehar', 'shehr', 'pohanchay', 'pohnche'])) {
            $reply = $isUrdu
                ? "🚚 **Delivery aur Coverage ki Maloomat**\n\n- **Coverage Areas:** Gilgit City, Skardu, Hunza Valley, Ghizer, Nagar, aur Astore.\n- **Delivery Charges:** Sirf **Rs 150** (Flat Rate poore order group par).\n- **Timing:** Subah ka taza harvest insulated food-grade cold boxes me deliver kiya jata hai.\n- **Special Air Shipments:** Islamabad aur Rawalpindi ke liye special cold air booking bhi dastiyab hai."
                : "🚚 **Delivery & Coverage Information**\n\n- **Coverage Areas:** Gilgit City, Skardu, Hunza Valley, Ghizer, Nagar, and Astore.\n- **Delivery Charges:** Flat **Rs 150** per order group.\n- **Dispatch Time:** Morning harvest dispatched daily in food-grade insulated cold boxes.\n- **Special Air Shipments:** Special refrigerated air bookings available for Islamabad / Rawalpindi.";

            return response()->json([
                'reply' => $reply,
                'type' => 'text',
                'suggestions' => [
                    $isUrdu ? '📦 Order Track Karein' : '📦 Track My Order',
                    $isUrdu ? '🥛 Taza Doodh & Ghee' : '🥛 Shop Fresh Dairy',
                    $isUrdu ? '💳 Payment ke Tareeqay' : '💳 Payment Methods',
                ],
            ]);
        }

        // 8. Payment Methods (COD, Bank Transfer)
        if (Str::contains($lowerMsg, ['payment', 'pay', 'cod', 'cash', 'jazzcash', 'easypaisa', 'bank transfer', 'card', 'online', 'paise', 'adaigi'])) {
            $reply = $isUrdu
                ? "💳 **Payment ke Dastiyab Tareeqay**\n\n- **Cash on Delivery (COD):** Delivery milne par rider ko cash ada karein.\n- **Online Bank Transfer / JazzCash / EasyPaisa:** Direct account me transfer kar ke receipt upload karein.\n- **100% Mehfooz:** Sirf verified farmers aur safe buyer protection."
                : "💳 **Payment Methods Supported**\n\n- **Cash on Delivery (COD):** Pay in cash when rider delivers fresh dairy to your doorstep.\n- **Online Bank Transfer / JazzCash / EasyPaisa:** Transfer directly and upload your transaction receipt for quick verification.\n- **100% Buyer Protection:** Safe transactions and verified farmers only.";

            return response()->json([
                'reply' => $reply,
                'type' => 'text',
                'suggestions' => [
                    $isUrdu ? '🛒 Shopping Shuru Karein' : '🛒 Start Shopping',
                    $isUrdu ? '📦 Order Track Karein' : '📦 Track Existing Order',
                    $isUrdu ? '🎁 Monthly Plans' : '🎁 Subscription Plans',
                ],
            ]);
        }

        // 9. Organic / Yak / Authenticity
        if (Str::contains($lowerMsg, ['organic', 'pure', 'khalis', 'asli', 'yak', 'grass-fed', 'chemical', 'test', 'quality', 'milawat', 'saaf'])) {
            $reply = $isUrdu
                ? "🏔️ **100% Khalis aur Pahaari Pasture-Fed Dairy**\n\nTamam dairy products Karakoram aur Himalaya ki qudrati charagahon se aati hain:\n- Zero chemical preservatives aur zero milawat.\n- Desi Gaye aur Yak ka khalis doodh, makhan aur ghee.\n- Har batch ka harvest date aur farmer record verified hota hai."
                : "🏔️ **100% Certified Organic & Pasture-Fed**\n\nAll our dairy comes from traditional high-altitude pasture grazing in the Karakoram & Himalayan valleys.\n- Zero synthetic additives or chemical preservatives.\n- Pure Desi Cow & Yak dairy processed with ancestral cleanliness standards.\n- Every batch includes harvest date & farmer batch tracking.";

            return response()->json([
                'reply' => $reply,
                'type' => 'text',
                'suggestions' => [
                    $isUrdu ? '🥛 Khalis Products Dekhein' : '🥛 View Pure Products',
                    $isUrdu ? '🧑‍🌾 Hamaray Farmers' : '🧑‍🌾 Meet Our Farmers',
                    $isUrdu ? '🚚 Delivery Details' : '🚚 Delivery Details',
                ],
                'actions' => [
                    [
                        'label' => $isUrdu ? 'Hamaray Farmers se Milein' : 'Meet Our Local Farmers',
                        'url' => route('farmers.index'),
                        'icon' => 'bi-people-fill',
                    ],
                ],
            ]);
        }

        // 10. Contact / Office / Human Support
        if (Str::contains($lowerMsg, ['contact', 'helpline', 'phone', 'number', 'call', 'human', 'agent', 'support', 'address', 'location', 'email', 'office', 'rabta', 'baat'])) {
            $phone = Setting::get('contact_phone', '+92 5811 455000');
            $email = Setting::get('contact_email', 'salam@organicdairy.test');

            $reply = $isUrdu
                ? "📞 **Customer Support & Helpline Maloomat**\n\n- **Phone / Call:** {$phone}\n- **Email:** {$email}\n- **Main Hub:** Organic Dairy Distribution Center, Jutial, Gilgit.\n- **Support Hours:** Somwar ta Itwar (7:00 AM – 9:00 PM)\n\nAap yahan live chat me bhi apna paigham chor sakte hain!"
                : "📞 **Customer Support & Hub Details**\n\n- **Phone / Helpline:** {$phone}\n- **Email:** {$email}\n- **Main Hub:** Organic Dairy Distribution Center, Jutial, Gilgit, Gilgit-Baltistan.\n- **Support Hours:** Monday to Sunday (7:00 AM – 9:00 PM PST)\n\nYou can also leave your message right here in this chat for instant assistance!";

            return response()->json([
                'reply' => $reply,
                'type' => 'contact_card',
                'suggestions' => [
                    $isUrdu ? '📦 Order Track Karein' : '📦 Track My Order',
                    $isUrdu ? '🥛 Products Dekhein' : '🥛 Browse Products',
                    $isUrdu ? '🎁 Daily Deals' : '🎁 Daily Deals',
                ],
            ]);
        }

        // 11. Greetings & Pleasantries (Strict whole-word boundary matching)
        if (preg_match('/\b(hi|hello|hey|salam|assalam|aoa|adaab|morning|evening|kese ho|kaisay ho|kaise ho)\b/i', $message)) {
            $reply = $isUrdu
                ? "Walaikum Assalam! 🏔️ **Organic Dairy Gilgit-Baltistan** me khush aamdeed!\n\nMain **Ayla** hoon, aapki customer support assistant. Main aapki kya madad kar sakti hoon? Aap taza doodh ke rates pooch sakte hain, apna order track kar sakte hain ya daily subscriptions le sakte hain!"
                : "Assalam-o-Alaikum! 🏔️ Welcome to **Organic Dairy Gilgit-Baltistan**.\n\nI am **Ayla**, your live customer support assistant. How may I assist you today? You can ask about our fresh products, track your delivery, or learn about our morning subscriptions!";

            return response()->json([
                'reply' => $reply,
                'type' => 'text',
                'suggestions' => [
                    $isUrdu ? '📦 Order Track Karein' : '📦 Track My Order',
                    $isUrdu ? '🥛 Taza Doodh & Desi Ghee' : '🥛 Pure Milk & Desi Ghee',
                    $isUrdu ? '🚚 Delivery Areas & Rates' : '🚚 Delivery Areas & Rates',
                    $isUrdu ? '🎁 Doodh Subscriptions' : '🎁 Milk Subscriptions',
                    $isUrdu ? '🧑‍🌾 Seller Banein' : '🧑‍🌾 Become a Seller',
                ],
            ]);
        }

        // 12. Check if Gemini AI LLM is configured
        $geminiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (!empty($geminiKey)) {
            $geminiResponse = $this->callGeminiLlm($message, $isUrdu, $geminiKey);
            if ($geminiResponse) {
                return $geminiResponse;
            }
        }

        // 13. Fallback Response
        $reply = $isUrdu
            ? "Shukriya **Organic Dairy Support** se rabta karne ka! 🥛\n\nMain aapki in chezon me madad kar sakti hoon:\n- **Order Track Karna** (apna Order ID jaise `ORD-XXXXXX` likhein)\n- **Taza Products & Rates** (Gaye ka Doodh, Yak Butter, Desi Ghee)\n- **Delivery Areas & Charges**\n- **Rozana Doodh Deals & Subscriptions**\n\nAap kya maloom karna chahte hain?"
            : "Thank you for reaching out to **Organic Dairy Support**! 🥛\n\nI can help you with:\n- **Tracking an Order** (type your order number like `ORD-XXXXXX`)\n- **Checking Fresh Products & Prices** (e.g. *Cow Milk, Yak Butter, Desi Ghee*)\n- **Delivery Information & Coverage**\n- **Daily Milk Subscriptions**\n\nWhat would you like to explore?";

        return response()->json([
            'reply' => $reply,
            'type' => 'fallback',
            'suggestions' => [
                $isUrdu ? '📦 Order Track Karein' : '📦 Track My Order',
                $isUrdu ? '🥛 Products & Rates' : '🥛 Fresh Products Catalog',
                $isUrdu ? '🚚 Delivery Maloomat' : '🚚 Delivery Rates & Cities',
                $isUrdu ? '🎁 Doodh Deals' : '🎁 Daily Subscriptions',
                $isUrdu ? '📞 Helpline' : '📞 Customer Helpline',
            ],
        ]);
    }

    /**
     * Handle inquiries about farms in specific locations.
     */
    protected function handleFarmLocationInquiry(string $originalMessage, string $lowerMsg, bool $isUrdu): JsonResponse
    {
        $location = null;

        // 1. Check pattern: "in oshikhandass", "at hunza", "near skardu"
        if (preg_match('/(?:in|at|near|from)\s+([a-zA-Z0-9\s-]+)/i', $originalMessage, $m)) {
            $candidate = trim(preg_replace('/[?!.,]/', '', $m[1]));
            $candidate = trim(preg_replace('/\b(available|please|village|city|area|dist|district|there|any|farm|farms|hai|kya)\b/i', '', $candidate));
            if ($candidate !== '') {
                $location = $candidate;
            }
        }

        // 2. Check Urdu pattern: "oshikhandass me", "skardu mai", "hunza mein"
        if (!$location && preg_match('/([a-zA-Z0-9-]+)\s+(?:me|mai|mein|kay|k|se)\b/i', $originalMessage, $m)) {
            $candidate = trim(preg_replace('/[?!.,]/', '', $m[1]));
            if (!in_array(strtolower($candidate), ['farm', 'koi', 'kya', 'is', 'ye', 'yeh', 'un', 'apke', 'aapke'])) {
                $location = $candidate;
            }
        }

        // 3. Check known GB locations
        if (!$location) {
            $gbPlaces = ['oshikhandass', 'hunza', 'skardu', 'gilgit', 'danyore', 'nagar', 'ghizer', 'astore', 'chilas', 'passu', 'aliabad', 'karimabad', 'khaplu', 'shigar', 'bagrote', 'jutial', 'nomal', 'gulmit', 'gupis', 'yasin', 'punial'];
            foreach ($gbPlaces as $place) {
                if (Str::contains($lowerMsg, $place)) {
                    $location = ucfirst($place);
                    break;
                }
            }
        }

        // Clean extracted location
        if ($location) {
            $location = trim($location);
        }

        if ($location && strlen($location) >= 2) {
            $farms = SellerProfile::approved()
                ->where(function ($q) use ($location) {
                    $q->where('location', 'like', "%{$location}%")
                      ->orWhere('farm_name', 'like', "%{$location}%")
                      ->orWhere('description', 'like', "%{$location}%");
                })
                ->with(['user.products' => function ($q) {
                    $q->where('status', 'approved');
                }])
                ->get();

            if ($farms->isNotEmpty()) {
                $farmCount = $farms->count();
                $farmDetails = $farms->map(function ($f) use ($isUrdu) {
                    $farmerName = $f->user->name ?? 'Verified Farmer';
                    $productCount = $f->approved_products_count;
                    $prodText = $isUrdu ? "({$productCount} Taza Products)" : "({$productCount} Active Products)";
                    return "• 🏡 **{$f->farm_name}**\n  📍 Location: `{$f->location}`\n  🧑‍🌾 Farmer: **{$farmerName}** {$prodText}";
                })->implode("\n\n");

                if ($isUrdu) {
                    $reply = "✅ **Jee Haan, {$location} me Farm Dastiyab Hai!**\n\n**{$location}** me hamaray pas **{$farmCount}** certified organic farm(s) active hain:\n\n{$farmDetails}\n\nAap in farms se 100% khalis gaye/yak ka doodh, desi ghee aur makhan direct order kar sakte hain!";
                } else {
                    $reply = "✅ **Yes, Farm Available in {$location}!**\n\nWe have **{$farmCount}** verified organic farm(s) registered in/near **{$location}**:\n\n{$farmDetails}\n\nYou can click on the farm below to view their profile, certifications, and fresh harvests!";
                }

                $actions = $farms->take(2)->map(function ($f) {
                    return [
                        'label' => "Visit {$f->farm_name}",
                        'url' => route('farmers.show', $f->id),
                        'icon' => 'bi-shop-window',
                    ];
                })->toArray();

                return response()->json([
                    'reply' => $reply,
                    'type' => 'farm_available',
                    'suggestions' => [
                        $isUrdu ? '🥛 Taza Products Dekhein' : '🥛 View Products',
                        $isUrdu ? '🚚 Delivery Charges' : '🚚 Delivery Rates',
                        $isUrdu ? '📦 Order Track Karein' : '📦 Track Order',
                    ],
                    'actions' => $actions,
                ]);
            } else {
                // Farm NOT registered directly in this location
                if ($isUrdu) {
                    $reply = "❌ **Nahi, {$location} me filhal koi direct farm registered nahi hai.**\n\nFilhal **`{$location}`** me hamara koi direct organic farm mojood nahi hai.\n\n🚚 **Lekin Delivery Dastiyab Hai!**\nHum Gilgit-Baltistan ke qareebi verified farms (Hunza, Skardu, Gilgit, Ghizer) se aapke ghar par taza pahaari doodh, makhhan aur desi ghee deliver karte hain (Flat Rs 150 delivery charges)!";
                } else {
                    $reply = "❌ **No, currently there is no farm registered directly in `{$location}`.**\n\nWe do not currently have an active farm partner based directly in **`{$location}`**.\n\n🚚 **However, we deliver to your doorstep!**\nYou can still order fresh organic cow/yak milk, butter, and desi ghee harvested from our nearby verified GB mountain farms with flat Rs 150 delivery!";
                }

                return response()->json([
                    'reply' => $reply,
                    'type' => 'farm_not_available',
                    'suggestions' => [
                        $isUrdu ? '🏡 Tamam Farms Dekhein' : '🏡 View All Farms',
                        $isUrdu ? '🥛 Taza Products Kharidein' : '🥛 Shop Dairy Products',
                        $isUrdu ? '🧑‍🌾 Apna Farm Register Karein' : '🧑‍🌾 Register as Farmer',
                    ],
                    'actions' => [
                        [
                            'label' => $isUrdu ? 'Tamam Registered Farms Dekhein' : 'View All Registered Farms',
                            'url' => route('farmers.index'),
                            'icon' => 'bi-people-fill',
                        ],
                    ],
                ]);
            }
        }

        // General farm inquiry without specific location
        $allFarms = SellerProfile::approved()->with('user')->take(4)->get();
        $farmList = $allFarms->map(function ($f) {
            return "• **{$f->farm_name}** (`{$f->location}`) — {$f->approved_products_count} Products";
        })->implode("\n");

        if ($isUrdu) {
            $reply = "🏡 **Hamaray Active Organic Farms**:\n\n{$farmList}\n\nTamam farms 100% pasture-fed aur certified hain. Kisi khaas elaqay (e.g. Hunza, Skardu, Gilgit) ke baray me poochna ho to location ka naam likhein!";
        } else {
            $reply = "🏡 **Our Active Mountain Dairy Farms**:\n\n{$farmList}\n\nAll farms are 100% certified and pasture-fed. To check a specific area (e.g. Hunza, Skardu, Gilgit, Oshikhandass), type the location name!";
        }

        return response()->json([
            'reply' => $reply,
            'type' => 'farm_list',
            'suggestions' => [
                $isUrdu ? '🥛 Taza Doodh & Ghee' : '🥛 Shop Fresh Dairy',
                $isUrdu ? '🚚 Delivery Areas' : '🚚 Delivery Areas',
                $isUrdu ? '🧑‍🌾 Farmer Banein' : '🧑‍🌾 Become a Seller',
            ],
            'actions' => [
                [
                    'label' => $isUrdu ? 'Tamam Farms Dekhein' : 'View All Farms',
                    'url' => route('farmers.index'),
                    'icon' => 'bi-people-fill',
                ],
            ],
        ]);
    }

    /**
     * Seller / Farmer registration guidance.
     */
    protected function handleSellerRegistrationInquiry(bool $isUrdu): JsonResponse
    {
        $reply = $isUrdu
            ? "🧑‍🌾 **Farmer / Seller ban kar Dairy Bechein**\n\nKya aap Gilgit-Baltistan ke local dairy farmer hain?\n1. Register karein aur **Farmer / Dairy Seller** select karein.\n2. Apne farm ka naam aur location darj karein.\n3. Admin approval ke baad aap foran apni dairy products upload kar sakte hain, direct orders le sakte hain aur bank/wallet me payments withdraw kar sakte hain!"
            : "🧑‍🌾 **Join as a Mountain Dairy Farmer / Seller**\n\nAre you a local dairy producer in Gilgit-Baltistan?\n1. Register your account and choose **Farmer / Dairy Seller**.\n2. Submit your farm name and location for verification.\n3. Once approved, you can directly upload and manage dairy harvests, set prices, and request direct bank/wallet payouts!";

        return response()->json([
            'reply' => $reply,
            'type' => 'text',
            'suggestions' => [
                $isUrdu ? '🧑‍🌾 Seller Register Karein' : '🧑‍🌾 Register as Seller',
                $isUrdu ? '🥛 Products Dekhein' : '🥛 Shop Products',
                $isUrdu ? '📞 Support Helpline' : '📞 Contact Support Team',
            ],
            'actions' => [
                [
                    'label' => $isUrdu ? 'Farmer Account Register Karein' : 'Register Farmer Account',
                    'url' => route('register'),
                    'icon' => 'bi-person-plus-fill',
                ],
            ],
        ]);
    }

    /**
     * Look up order details for tracking query.
     */
    protected function handleOrderLookup(string $orderIdentifier, bool $isUrdu = false): JsonResponse
    {
        $query = Order::query()->with(['seller.sellerProfile', 'items.product', 'orderGroup.payment']);

        $order = (clone $query)->where('order_no', $orderIdentifier)->first();

        if (!$order) {
            $order = (clone $query)->where('order_no', 'like', "%{$orderIdentifier}%")->first();
        }

        if (!$order && is_numeric($orderIdentifier)) {
            $order = (clone $query)->where('id', (int) $orderIdentifier)->first();
        }

        if (!$order) {
            $reply = $isUrdu
                ? "⚠️ Maazrat, **`{$orderIdentifier}`** ke mutabiq koi order nahi mila.\n\nBaraye meharbani SMS/Email me mila hua Order ID check karein ya neechay live order tracker me search karein."
                : "⚠️ Could not find an order matching **`{$orderIdentifier}`**.\n\nPlease double-check the Order ID received in your SMS/Email, or click below to search in the order tracker.";

            return response()->json([
                'reply' => $reply,
                'type' => 'order_not_found',
                'suggestions' => [
                    $isUrdu ? 'Live Tracker Kholein' : 'Track on Tracking Page',
                    $isUrdu ? '🥛 Taza Doodh Kharidein' : '🥛 Shop Fresh Milk',
                    $isUrdu ? '📞 Support se Rabta' : '📞 Contact Support',
                ],
                'actions' => [
                    [
                        'label' => $isUrdu ? 'Order Tracker Kholein' : 'Open Order Tracker',
                        'url' => route('orders.track'),
                        'icon' => 'bi-search',
                    ],
                ],
            ]);
        }

        $statusTitlesEn = [
            'placed' => 'Order Placed (Waiting for Confirmation)',
            'new' => 'Confirmed by Farm',
            'confirmed' => 'Order Confirmed',
            'packing' => 'Harvesting & Cold Packing',
            'in_transit' => 'Out for Doorstep Delivery',
            'delivered' => 'Delivered Successfully ✅',
            'cancelled' => 'Cancelled',
        ];

        $statusTitlesUrdu = [
            'placed' => 'Order Place ho chuka hai (Confirmation ka intezar)',
            'new' => 'Farm ne Confirm kar diya hai',
            'confirmed' => 'Order Confirmed hai',
            'packing' => 'Taza Harvest aur Cold Packing jari hai',
            'in_transit' => 'Delivery rider nikal chuka hai (Out for Delivery)',
            'delivered' => 'Pohanch chuka hai (Delivered) ✅',
            'cancelled' => 'Cancel ho gaya hai',
        ];

        $statusText = $isUrdu 
            ? ($statusTitlesUrdu[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)))
            : ($statusTitlesEn[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)));

        $itemCount = $order->items->count();
        $totalFormatted = 'Rs ' . number_format($order->total_amount);
        $placedOn = $order->created_at ? $order->created_at->format('M d, Y - h:i A') : 'N/A';
        $farmName = $order->seller->sellerProfile->farm_name ?? $order->seller->name ?? 'Organic Dairy Partner';

        $itemsSummary = $order->items->take(3)->map(function ($item) {
            return "• {$item->product_name} (x{$item->quantity})";
        })->implode("\n");

        if ($itemCount > 3) {
            $moreText = $isUrdu ? "aur " . ($itemCount - 3) . " mazeed items..." : "and " . ($itemCount - 3) . " more item(s)...";
            $itemsSummary .= "\n• {$moreText}";
        }

        if ($isUrdu) {
            $reply = "📦 **Order #{$order->order_no} ki Live Detail**\n\n" .
                     "**Status:** `{$statusText}`\n" .
                     "**Farm Partner:** {$farmName}\n" .
                     "**Tarykh:** {$placedOn}\n" .
                     "**Kul Raqam:** **{$totalFormatted}** (" . strtoupper($order->payment_status) . ")\n\n" .
                     "**Items:**\n{$itemsSummary}\n\n" .
                     "Live rider timeline aur tracking dekhne ke liye neechay diye gaye button par click karein!";
        } else {
            $reply = "📦 **Order Status for #{$order->order_no}**\n\n" .
                     "**Status:** `{$statusText}`\n" .
                     "**Farm Partner:** {$farmName}\n" .
                     "**Placed On:** {$placedOn}\n" .
                     "**Total Amount:** **{$totalFormatted}** (" . strtoupper($order->payment_status) . ")\n\n" .
                     "**Items in this Shipment:**\n{$itemsSummary}\n\n" .
                     "Click below to see live real-time timeline steps & rider notes!";
        }

        return response()->json([
            'reply' => $reply,
            'type' => 'order_card',
            'order' => [
                'order_no' => $order->order_no,
                'status' => $order->status,
                'status_label' => $statusText,
                'total' => $totalFormatted,
                'payment_status' => $order->payment_status,
                'farm_name' => $farmName,
            ],
            'suggestions' => [
                $isUrdu ? '🚚 Delivery Maloomat' : '🚚 Delivery Details',
                $isUrdu ? '🥛 Mazeed Products' : '🥛 Buy More Products',
                $isUrdu ? '📞 Helpline' : '📞 Contact Support',
            ],
            'actions' => [
                [
                    'label' => $isUrdu ? "Live Track Karein #{$order->order_no}" : "Live Track #{$order->order_no}",
                    'url' => route('orders.track', $order->order_no),
                    'icon' => 'bi-geo-alt-fill',
                ],
            ],
        ]);
    }

    /**
     * Look up product catalog details matching keywords.
     */
    protected function handleProductInquiry(string $query, bool $isUrdu = false): JsonResponse
    {
        $productsQuery = Product::approved()
            ->with(['category', 'seller.sellerProfile', 'images'])
            ->latest();

        // 1. Check for specific categories or item types
        if (Str::contains($query, ['yak', 'churpi', 'yak butter', 'yak milk'])) {
            $productsQuery->where(function ($q) {
                $q->where('name', 'like', '%yak%')
                  ->orWhere('description', 'like', '%yak%');
            });
        } elseif (Str::contains($query, ['ghee', 'desi ghee'])) {
            $productsQuery->where(function ($q) {
                $q->where('name', 'like', '%ghee%')
                  ->orWhere('description', 'like', '%ghee%');
            });
        } elseif (Str::contains($query, ['butter', 'makhan'])) {
            $productsQuery->where(function ($q) {
                $q->where('name', 'like', '%butter%')
                  ->orWhere('name', 'like', '%makhan%')
                  ->orWhere('description', 'like', '%butter%');
            });
        } elseif (Str::contains($query, ['cheese', 'paneer'])) {
            $productsQuery->where(function ($q) {
                $q->where('name', 'like', '%cheese%')
                  ->orWhere('name', 'like', '%paneer%')
                  ->orWhere('description', 'like', '%cheese%');
            });
        } elseif (Str::contains($query, ['honey', 'shahad'])) {
            $productsQuery->where(function ($q) {
                $q->where('name', 'like', '%honey%')
                  ->orWhere('name', 'like', '%shahad%')
                  ->orWhere('description', 'like', '%honey%');
            });
        } elseif (Str::contains($query, ['milk', 'doodh'])) {
            $productsQuery->where(function ($q) {
                $q->where('name', 'like', '%milk%')
                  ->orWhere('name', 'like', '%doodh%');
            });
        } else {
            // Dynamic multi-word matching for any newly added products
            $words = array_filter(preg_split('/\s+/', $query), function ($w) {
                $w = trim($w, ".,?!:;\"'");
                return strlen($w) >= 3 && !in_array($w, ['rate', 'price', 'kya', 'hai', 'hain', 'kitna', 'show', 'tell', 'about', 'fresh', 'taza', 'keemat', 'chahiye']);
            });

            if (!empty($words)) {
                $productsQuery->where(function ($q) use ($words) {
                    foreach ($words as $word) {
                        $cleanWord = trim($word, ".,?!:;\"'");
                        $q->orWhere('name', 'like', "%{$cleanWord}%")
                          ->orWhere('description', 'like', "%{$cleanWord}%");
                    }
                });
            }
        }

        $products = $productsQuery->take(4)->get();

        if ($products->isEmpty()) {
            $products = Product::approved()
                ->with(['category', 'seller.sellerProfile', 'images'])
                ->take(4)
                ->get();
        }

        $listText = $products->map(function ($p) use ($isUrdu) {
            $farm = $p->seller->sellerProfile->farm_name ?? 'Mountain Farm';
            $stockText = $p->stock > 0 ? ($isUrdu ? '[Dastiyab hai]' : '[In Stock]') : ($isUrdu ? '[Kam Stock]' : '[Limited Stock]');
            return "• **{$p->name}** — `{$p->formatted_price} / {$p->unit}` (*{$farm}*) {$stockText}";
        })->implode("\n");

        if ($isUrdu) {
            $reply = "🥛 **Aaj ke Taza Pahaari Dairy Products aur Rates**:\n\n" .
                     "{$listText}\n\n" .
                     "Tamam items 100% khalis, pasture-fed aur rozana subah fresh harvest hote hain!";
        } else {
            $reply = "🥛 **Fresh Mountain Dairy Products Available Today**:\n\n" .
                     "{$listText}\n\n" .
                     "All items are 100% pure, pasture-fed, and harvested daily with cold-chain delivery!";
        }

        $productCards = $products->map(function ($p) {
            return [
                'name' => $p->name,
                'price' => $p->formatted_price,
                'unit' => $p->unit,
                'url' => route('shop.show', $p->slug),
                'farm' => $p->seller->sellerProfile->farm_name ?? 'Local Farm',
            ];
        });

        return response()->json([
            'reply' => $reply,
            'type' => 'product_list',
            'products' => $productCards,
            'suggestions' => [
                $isUrdu ? '🚚 Delivery Maloomat' : '🚚 Delivery Coverage',
                $isUrdu ? '🎁 Doodh Subscriptions' : '🎁 Milk Subscriptions',
                $isUrdu ? '📦 Order Track Karein' : '📦 Track My Order',
            ],
            'actions' => [
                [
                    'label' => $isUrdu ? 'Poori Shop Dekhein' : 'View Full Shop Catalog',
                    'url' => route('shop.index'),
                    'icon' => 'bi-shop',
                ],
            ],
        ]);
    }

    /**
     * Helper to detect if user query is in Roman Urdu.
     */
    protected function isRomanUrdu(string $text): bool
    {
        $urduWords = [
            'kya', 'hai', 'hain', 'kaise', 'kese', 'kahan', 'kidhar', 'kab', 'kitna', 'kitne',
            'chahiye', 'chahye', 'chaho', 'batao', 'bataen', 'bataiye', 'doodh', 'makhan', 'ghee',
            'khalis', 'asli', 'bechna', 'kharidna', 'mangwana', 'mangwaye', 'bhejo', 'kar do',
            'mujhe', 'mera', 'meri', 'mere', 'apka', 'apki', 'apke', 'humara', 'ye', 'yeh',
            'wo', 'woh', 'bhi', 'nahi', 'nai', 'shukriya', 'theek', 'acha', 'acha ji',
            'mil', 'milega', 'milegi', 'raha', 'rahi', 'rahe', 'karo', 'karna', 'karta',
            'banao', 'bana', 'bata do', 'hoga', 'hogi', 'honge', 'pe', 'par', 'se', 'ko', 'ka', 'ki', 'ke',
            'salam', 'walaikum', 'walekum', 'aoa', 'kuch', 'btao', 'kro', 'sirf', 'shahr', 'shehar'
        ];

        $tokens = preg_split('/\s+/', strtolower($text));
        foreach ($tokens as $token) {
            $clean = trim($token, ".,?!:;\"'()[]{}");
            if (in_array($clean, $urduWords, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Call Google Gemini API if configured in .env.
     */
    protected function callGeminiLlm(string $userMessage, bool $isUrdu, string $apiKey): ?JsonResponse
    {
        try {
            $model = config('services.gemini.model', 'gemini-1.5-flash');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            // Gather live summary of products and farms as context
            $productsList = Product::approved()->take(6)->get()->map(function ($p) {
                return "{$p->name} (Rs {$p->price} / {$p->unit})";
            })->implode(', ');

            $farmsList = SellerProfile::approved()->take(6)->get()->map(function ($f) {
                return "{$f->farm_name} in {$f->location}";
            })->implode(', ');

            $langInstruction = $isUrdu 
                ? "Reply in natural, polite Roman Urdu (Urdu written in English alphabets). Keep your reply concise (2-4 sentences max)."
                : "Reply in fluent English. Keep your reply concise (2-4 sentences max).";

            $systemPrompt = "You are Ayla, an AI customer support assistant for Organic Dairy Gilgit-Baltistan (marketplace connecting local mountain farmers with consumers). " .
                "Current live products: {$productsList}. " .
                "Current registered farms: {$farmsList}. " .
                "Delivery policy: Flat Rs 150 across Gilgit, Skardu, Hunza, Ghizer, Nagar, Astore in insulated cold boxes. Payment: Cash on Delivery (COD) & Online Transfer. " .
                "{$langInstruction}";

            $response = Http::timeout(6)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "{$systemPrompt}\n\nUser Question: {$userMessage}"]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $body = $response->json();
                $generatedText = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($generatedText) {
                    return response()->json([
                        'reply' => trim($generatedText),
                        'type' => 'gemini_response',
                        'suggestions' => [
                            $isUrdu ? '📦 Order Track Karein' : '📦 Track My Order',
                            $isUrdu ? '🥛 Taza Products' : '🥛 Fresh Products',
                            $isUrdu ? '🚚 Delivery Maloomat' : '🚚 Delivery Rates',
                        ],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gemini API call failed: ' . $e->getMessage());
        }

        return null;
    }
}
