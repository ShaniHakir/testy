<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Services\CurrencyConversionService;

class OrderController extends Controller
{
    protected $currencyConversionService;

    public function __construct(CurrencyConversionService $currencyConversionService)
    {
        $this->currencyConversionService = $currencyConversionService;
    }

    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'vendor') {
            $orders = Order::with(['user', 'orderItems.product'])
                ->whereHas('orderItems', function ($query) use ($user) {
                    $query->where('vendor_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
            return view('orders.vendor_manage', compact('orders'));
        } else {
            $orders = Order::with(['orderItems.product'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            return view('orders.index', compact('orders'));
        }
    }

    public function show(Order $order)
    {
        if (Gate::denies('view', $order)) {
            abort(403, 'Unauthorized action.');
        }
    
        $order->load(['orderItems.product', 'user']);
    
        return view('orders.show', [
            'order' => $order,
            'currencyConversionService' => $this->currencyConversionService
        ]);
    }

    public function accept(Order $order)
    {
        if (Gate::denies('update', $order)) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($order) {
            $user = auth()->user();
            $orderItems = $order->orderItems()->where('vendor_id', $user->id)->get();
            
            foreach ($orderItems as $item) {
                $item->status = 'processing';
                $item->save();
            }

            if ($order->allItemsHaveStatus('processing')) {
                $order->status = Order::STATUS_PROCESSING;
                $order->save();
            }
        });

        return redirect()->route('orders.index')->with('success', 'Order accepted successfully.');
    }

    public function reject(Order $order)
    {
        if (Gate::denies('update', $order)) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($order) {
            $user = auth()->user();
            $orderItems = $order->orderItems()->where('vendor_id', $user->id)->get();
            
            foreach ($orderItems as $item) {
                $item->status = 'rejected';
                $item->save();

                // Update BTC price at the time of rejection
                $updatedPriceBtc = $this->currencyConversionService->convertUsdToBtc($item->price_usd);
                $exchangeRate = $item->price_usd / $updatedPriceBtc;

                \Log::info("Rejecting order item", [
                    'item_id' => $item->id,
                    'price_usd' => $item->price_usd,
                    'original_price_btc' => $item->price_btc,
                    'updated_price_btc' => $updatedPriceBtc,
                    'exchange_rate' => $exchangeRate,
                    'quantity' => $item->quantity
                ]);

                // Return the funds to the user's wallet using the updated BTC price
                $buyer = User::find($order->user_id);
                $buyerWallet = $this->getOrCreateWallet($buyer);
                $refundAmount = $updatedPriceBtc * $item->quantity;
                $buyerWallet->balance_btc += $refundAmount;
                $buyerWallet->save();

                \Log::info("Refund processed", [
                    'buyer_id' => $buyer->id,
                    'refund_amount_btc' => $refundAmount,
                    'wallet_balance_btc' => $buyerWallet->balance_btc
                ]);

                // Return the stock to the product
                $product = $item->product;
                $product->stock += $item->quantity;
                $product->save();

                \Log::info("Product stock updated", [
                    'product_id' => $product->id,
                    'quantity_returned' => $item->quantity,
                    'new_stock' => $product->stock
                ]);
            }

            if ($order->allItemsHaveStatus('rejected')) {
                $order->status = Order::STATUS_REJECTED;
            } elseif (!$order->orderItems()->where('status', 'pending')->exists()) {
                $order->status = Order::STATUS_PROCESSING;
            }
            $order->save();

            \Log::info("Order status updated", [
                'order_id' => $order->id,
                'new_status' => $order->status
            ]);
        });

        return redirect()->route('orders.index')->with('success', 'Order rejected successfully.');
    }

    public function complete(Order $order)
    {
        if (Gate::denies('update', $order)) {
            abort(403, 'Unauthorized action.');
        }
    
        DB::transaction(function () use ($order) {
            $user = auth()->user();
            $orderItems = $order->orderItems()->where('vendor_id', $user->id)->get();
            
            $marketplaceFeePercentage = env('MARKETPLACE_FEE_PERCENTAGE', 5) / 100;
            $marketplaceFeeRecipientUsername = env('MARKETPLACE_FEE_RECIPIENT_USERNAME', 'admin');
    
            $marketplaceFeeRecipient = User::where('username', $marketplaceFeeRecipientUsername)->first();
    
            if (!$marketplaceFeeRecipient) {
                \Log::error("Marketplace fee recipient not found");
                throw new \Exception("Marketplace fee recipient not found");
            }
    
            $totalAmountBtc = 0;
            $totalMarketplaceFee = 0;
            $totalVendorAmount = 0;
    
            foreach ($orderItems as $item) {
                $singleItemPriceUsd = $item->price_usd / $item->quantity;
                $singleItemPriceBtc = $this->currencyConversionService->convertUsdToBtc($singleItemPriceUsd);
                $totalItemAmountBtc = $singleItemPriceBtc * $item->quantity;
                $exchangeRate = $singleItemPriceUsd / $singleItemPriceBtc;

                \Log::info("Processing order item", [
                    'item_id' => $item->id,
                    'single_item_price_usd' => $singleItemPriceUsd,
                    'single_item_price_btc' => $singleItemPriceBtc,
                    'exchange_rate' => $exchangeRate,
                    'quantity' => $item->quantity,
                    'total_item_amount_btc' => $totalItemAmountBtc
                ]);
    
                if ($singleItemPriceBtc <= 0 || $item->quantity <= 0) {
                    \Log::warning("Invalid price or quantity for order item", [
                        'item_id' => $item->id,
                        'single_item_price_btc' => $singleItemPriceBtc,
                        'quantity' => $item->quantity
                    ]);
                    continue;
                }
    
                $item->status = 'delivered';
                $item->price_btc = $singleItemPriceBtc;
                $item->save();
    
                $marketplaceFee = $totalItemAmountBtc * $marketplaceFeePercentage;
                $vendorAmount = $totalItemAmountBtc - $marketplaceFee;
    
                $totalAmountBtc += $totalItemAmountBtc;
                $totalMarketplaceFee += $marketplaceFee;
                $totalVendorAmount += $vendorAmount;
            }
    
            // Transfer the funds to the vendor's wallet
            $vendorWallet = $this->getOrCreateWallet($user);
            $vendorWallet->balance_btc += $totalVendorAmount;
            $vendorWallet->save();
    
            // Transfer the marketplace fee to the marketplace fee recipient's wallet
            $recipientWallet = $this->getOrCreateWallet($marketplaceFeeRecipient);
            $recipientWallet->balance_btc += $totalMarketplaceFee;
            $recipientWallet->save();
    
            \Log::info("Order processed", [
                'order_id' => $order->id,
                'vendor_id' => $user->id,
                'total_amount_btc' => $totalAmountBtc,
                'total_marketplace_fee_btc' => $totalMarketplaceFee,
                'total_vendor_amount_btc' => $totalVendorAmount,
                'vendor_wallet_balance' => $vendorWallet->balance_btc,
                'recipient_wallet_balance' => $recipientWallet->balance_btc
            ]);
    
            if ($order->allItemsHaveStatus('delivered')) {
                $order->status = Order::STATUS_COMPLETED;
                $order->save();
            }
        });
    
        return redirect()->route('orders.index')->with('success', 'Order marked as completed successfully.');
    }
    
    private function getOrCreateWallet(User $user)
    {
        $wallet = $user->wallet;
    
        if (!$wallet) {
            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->balance_btc = 0;
            $wallet->save();
            $user->wallet()->save($wallet);
        }
    
        \Log::info("Wallet retrieved or created", [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'balance_btc' => $wallet->balance_btc
        ]);
    
        return $wallet;
    }
}
