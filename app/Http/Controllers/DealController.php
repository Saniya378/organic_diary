<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Category;
use App\Models\Deal;
use App\Models\DealDelivery;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DealController extends Controller
{
    /**
     * Public Showcase of Farm Deals & Subscriptions (3 Days, 1 Week, 1 Month)
     */
    public function index(Request $request): View
    {
        $durationFilter = $request->query('duration'); // 3_days, 1_week, 1_month
        $categoryFilter = $request->query('category');

        $query = Product::approved()->dealsEnabled()->with(['category', 'images', 'seller.sellerProfile']);

        if ($categoryFilter) {
            $query->whereHas('category', fn($q) => $q->where('slug', $categoryFilter));
        }

        $products = $query->paginate(9)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        $disc3 = (int) Setting::get('deal_discount_3_days', '5');
        $disc1w = (int) Setting::get('deal_discount_1_week', '10');
        $disc1m = (int) Setting::get('deal_discount_1_month', '15');

        // Deal Plans Configuration
        $dealPlans = [
            '3_days' => [
                'name' => '3 Days Trial Deal',
                'days' => 3,
                'discount' => $disc3,
                'badge' => "{$disc3}% OFF",
                'description' => 'Perfect for tasting fresh mountain dairy straight from the morning batch with zero long-term commitment.',
                'icon' => 'bi-calendar-check',
                'color' => '#1F3A27',
            ],
            '1_week' => [
                'name' => '1 Week Routine Deal',
                'days' => 7,
                'discount' => $disc1w,
                'badge' => "{$disc1w}% OFF",
                'description' => 'A full week of pure organic morning deliveries. Ideal for regular household breakfast routines.',
                'icon' => 'bi-calendar-week',
                'color' => '#C49A45',
            ],
            '1_month' => [
                'name' => '1 Month Family Supply Deal',
                'days' => 30,
                'discount' => $disc1m,
                'badge' => "{$disc1m}% OFF - Best Value",
                'description' => '30 consecutive days of hassle-free doorstep supply from verified high-altitude valley farms.',
                'icon' => 'bi-calendar-month',
                'color' => '#2B4A34',
            ],
        ];

        return view('deals.index', [
            'products' => $products,
            'categories' => $categories,
            'dealPlans' => $dealPlans,
            'selectedDuration' => $durationFilter,
            'selectedCategory' => $categoryFilter,
        ]);
    }

    /**
     * Show Deal Configuration & Subscribe Form
     */
    public function create(Product $product, Request $request): View|RedirectResponse
    {
        if ($product->status !== 'approved' || !$product->deals_enabled) {
            return redirect()->route('deals.index')->with('error', 'The seller has not enabled recurring deals for this product.');
        }

        $duration = $request->query('duration', '1_week');
        $addresses = Auth::check() ? Auth::user()->addresses : collect();

        $discounts = [
            '3_days' => (int) Setting::get('deal_discount_3_days', '5'),
            '1_week' => (int) Setting::get('deal_discount_1_week', '10'),
            '1_month' => (int) Setting::get('deal_discount_1_month', '15'),
        ];
        $days = [
            '3_days' => 3,
            '1_week' => 7,
            '1_month' => 30,
        ];

        return view('deals.create', [
            'product' => $product->load(['seller.sellerProfile', 'variants', 'images']),
            'initialDuration' => in_array($duration, ['3_days', '1_week', '1_month']) ? $duration : '1_week',
            'discounts' => $discounts,
            'days' => $days,
            'addresses' => $addresses,
        ]);
    }

    /**
     * Store new Deal and generate scheduled deliveries
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'duration_type' => 'required|in:3_days,1_week,1_month',
            'quantity_per_day' => 'required|integer|min:1|max:20',
            'delivery_slot' => 'required|in:morning,evening',
            'start_date' => 'required|date|after_or_equal:today',
            'payment_method' => 'required|in:cod,card',
            'address_id' => 'nullable|exists:addresses,id',
            'full_name' => 'required_without:address_id|nullable|string|max:100',
            'phone' => 'required_without:address_id|nullable|string|max:20',
            'city' => 'required_without:address_id|nullable|string|max:50',
            'area' => 'required_without:address_id|nullable|string|max:100',
            'address_line' => 'required_without:address_id|nullable|string|max:255',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $product = Product::with('seller')->findOrFail($request->product_id);
        if ($product->status !== 'approved' || !$product->deals_enabled) {
            return back()->with('error', 'The seller has not enabled recurring deals for this product.');
        }

        $user = Auth::user();

        // Resolve or create Address
        $addressId = $request->address_id;
        if (!$addressId && $request->address_line) {
            $newAddr = Address::create([
                'user_id' => $user->id,
                'full_name' => $request->full_name ?? $user->name,
                'phone' => $request->phone ?? $user->phone,
                'city' => $request->city ?? 'Gilgit',
                'area' => $request->area ?? '',
                'address_line' => $request->address_line,
                'is_default' => $user->addresses()->count() === 0,
            ]);
            $addressId = $newAddr->id;
        }

        // Deal Duration & Discount Calculation
        $daysConfig = [
            '3_days' => ['days' => 3, 'discount' => (int) Setting::get('deal_discount_3_days', '5'), 'label' => '3 Days Trial Deal'],
            '1_week' => ['days' => 7, 'discount' => (int) Setting::get('deal_discount_1_week', '10'), 'label' => '1 Week Routine Deal'],
            '1_month' => ['days' => 30, 'discount' => (int) Setting::get('deal_discount_1_month', '15'), 'label' => '1 Month Family Supply Deal'],
        ];

        $plan = $daysConfig[$request->duration_type];
        $totalDays = $plan['days'];
        $discountPercent = $plan['discount'];

        $unitPrice = $product->price;
        $qtyPerDay = (int) $request->quantity_per_day;
        $subtotal = $unitPrice * $qtyPerDay * $totalDays;
        $discountAmount = (int) round(($subtotal * $discountPercent) / 100);
        $totalAmount = $subtotal - $discountAmount;

        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addDays($totalDays - 1);

        $deal = DB::transaction(function () use ($user, $product, $addressId, $plan, $request, $totalDays, $qtyPerDay, $unitPrice, $discountPercent, $totalAmount, $startDate, $endDate) {
            $deal = Deal::create([
                'deal_no' => Deal::generateDealNo(),
                'buyer_id' => $user->id,
                'seller_id' => $product->seller_id,
                'product_id' => $product->id,
                'product_variant_id' => $request->product_variant_id ?: null,
                'address_id' => $addressId,
                'title' => "{$plan['label']} - {$product->name}",
                'duration_type' => $request->duration_type,
                'total_days' => $totalDays,
                'delivered_days' => 0,
                'quantity_per_day' => $qtyPerDay,
                'unit_price' => $unitPrice,
                'discount_percent' => $discountPercent,
                'total_amount' => $totalAmount,
                'delivery_slot' => $request->delivery_slot,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'status' => 'active',
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'card' ? 'paid' : 'pending',
                'special_instructions' => $request->special_instructions,
            ]);

            // Automatically Schedule All Deliveries
            for ($i = 1; $i <= $totalDays; $i++) {
                DealDelivery::create([
                    'deal_id' => $deal->id,
                    'day_number' => $i,
                    'scheduled_date' => $startDate->copy()->addDays($i - 1)->toDateString(),
                    'status' => 'scheduled',
                ]);
            }

            // Notification for Seller
            Notification::create([
                'user_id' => $product->seller_id,
                'title' => 'New Deal Started!',
                'body' => "Customer {$user->name} has started a {$plan['label']} for {$product->name} ({$qtyPerDay}x daily).",
                'type' => 'deal',
            ]);

            return $deal;
        });

        return redirect()->route('buyer.deals.show', $deal->id)
            ->with('success', "Congratulations! Your {$plan['label']} has been activated successfully.");
    }

    /**
     * Buyer's Subscriptions / Deals Dashboard
     */
    public function buyerIndex(): View
    {
        $deals = Deal::where('buyer_id', Auth::id())
            ->with(['product.images', 'seller.sellerProfile', 'deliveries'])
            ->latest()
            ->paginate(10);

        return view('deals.buyer_index', [
            'deals' => $deals,
        ]);
    }

    /**
     * View Deal Details, Progress and Calendar
     */
    public function buyerShow(Deal $deal): View|RedirectResponse
    {
        if ($deal->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this deal.');
        }

        $deal->load(['product.images', 'seller.sellerProfile', 'deliveries', 'address']);

        return view('deals.buyer_show', [
            'deal' => $deal,
        ]);
    }

    /**
     * Pause Deal
     */
    public function pause(Deal $deal): RedirectResponse
    {
        if ($deal->buyer_id !== Auth::id()) {
            abort(403);
        }

        if ($deal->status !== 'active') {
            return back()->with('error', 'Only active deals can be paused.');
        }

        $deal->update(['status' => 'paused']);

        Notification::create([
            'user_id' => $deal->seller_id,
            'title' => 'Deal Paused by Customer',
            'body' => "Customer {$deal->buyer->name} has paused deal {$deal->deal_no} temporarily.",
            'type' => 'deal',
        ]);

        return back()->with('success', 'Your deal deliveries have been temporarily paused.');
    }

    /**
     * Resume Deal
     */
    public function resume(Deal $deal): RedirectResponse
    {
        if ($deal->buyer_id !== Auth::id()) {
            abort(403);
        }

        if ($deal->status !== 'paused') {
            return back()->with('error', 'Only paused deals can be resumed.');
        }

        $deal->update(['status' => 'active']);

        Notification::create([
            'user_id' => $deal->seller_id,
            'title' => 'Deal Resumed by Customer',
            'body' => "Customer {$deal->buyer->name} has resumed deal {$deal->deal_no}.",
            'type' => 'deal',
        ]);

        return back()->with('success', 'Your deal deliveries have been resumed.');
    }

    /**
     * Cancel Deal
     */
    public function cancel(Deal $deal): RedirectResponse
    {
        if ($deal->buyer_id !== Auth::id()) {
            abort(403);
        }

        if (in_array($deal->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This deal cannot be cancelled.');
        }

        $deal->update(['status' => 'cancelled']);

        Notification::create([
            'user_id' => $deal->seller_id,
            'title' => 'Deal Cancelled by Customer',
            'body' => "Customer {$deal->buyer->name} has cancelled deal {$deal->deal_no}.",
            'type' => 'deal',
        ]);

        return back()->with('success', 'Deal has been cancelled.');
    }
}
