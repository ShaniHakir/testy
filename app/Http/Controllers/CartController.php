<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Events\OrderPlaced;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InsufficientStockException;
use App\Services\CurrencyConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\CartItem;


class CartController extends Controller
{
    protected $currencyConversionService;

    public function __construct(CurrencyConversionService $currencyConversionService)
    {
        $this->currencyConversionService = $currencyConversionService;
    }

    public function index()
    {
        $cart = Auth::user()->cart;
        $cartItems = $cart ? $cart->items : collect();
    
        foreach ($cartItems as $item) {
            Log::info("Cart Item", [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'stored_price' => $item->price,
                'current_price' => $item->getCurrentPriceInUsd(),
            ]);
        }
    
        return view('cart.index', [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'currencyConversionService' => $this->currencyConversionService
        ]);
    }

    public function addToCart(Request $request, Product $product)
    {
        Log::info('Adding product to cart', [
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'quantity' => $request->quantity
        ]);
    
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
    
        // Check if there's enough stock
        if ($product->stock < $request->quantity) {
            Log::warning('Insufficient stock', [
                'product_id' => $product->id,
                'requested_quantity' => $request->quantity,
                'available_stock' => $product->stock
            ]);
            return redirect()->back()->with('error', "Insufficient stock for {$product->name}. Only {$product->stock} available.");
        }

        $cart = Auth::user()->cart ?? Cart::create(['user_id' => Auth::id()]);

        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        $currentPrice = $product->getCurrentPriceInUsd($request->quantity);

        if ($cartItem) {
            // Check if the new total quantity exceeds the available stock
            if ($product->stock < ($cartItem->quantity + $request->quantity)) {
                Log::warning('Insufficient stock for additional quantity', [
                    'product_id' => $product->id,
                    'current_quantity' => $cartItem->quantity,
                    'requested_quantity' => $request->quantity,
                    'available_stock' => $product->stock
                ]);
                return redirect()->back()->with('error', "Cannot add {$request->quantity} more of {$product->name} to cart. Only {$product->stock} available in total.");
            }

            $cartItem->quantity += $request->quantity;
            $cartItem->price = $currentPrice;
            $cartItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $currentPrice,
            ]);
        }

        Log::info("Added to cart", [
            'product_id' => $product->id,
            'price' => $currentPrice,
            'quantity' => $request->quantity
        ]);

        return redirect()->route('cart.index')->with('success', 'Product added to cart.');
    }

    public function removeFromCart($cartItemId)
    {
        $cartItem = CartItem::findOrFail($cartItemId);
        
        if ($cartItem->cart->user_id !== Auth::id()) {
            return redirect()->route('cart.index')->with('error', 'Unauthorized action.');
        }
        
        $cartItem->delete();
        return redirect()->route('cart.index')->with('success', 'Product removed from cart.');
    }

    public function checkout()
    {
        $user = Auth::user();
        $cart = $user->cart;
        $wallet = $user->wallet;
    
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
    
        $totalUsd = 0;
        DB::beginTransaction();
    
        try {
            // Calculate totals first
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
    
                if ($product->stock < $cartItem->quantity) {
                    throw new InsufficientStockException("Insufficient stock for {$product->name}. Only {$product->stock} available.");
                }
    
                $itemPriceUsd = $product->getCurrentPriceInUsd($cartItem->quantity) * $cartItem->quantity;
                $totalUsd += $itemPriceUsd;
            }
    
            $totalBtc = $this->currencyConversionService->convertUsdToBtc($totalUsd);
    
            Log::info('Checkout process', [
                'total_usd' => $totalUsd,
                'total_btc' => $totalBtc,
                'wallet_balance' => $wallet->balance_btc
            ]);
    
            if ($wallet->balance_btc < $totalBtc) {
                throw new InsufficientFundsException('Insufficient funds in wallet.');
            }
    
            $order = new Order();
            $order->user_id = $user->id;
            $order->total_amount_usd = $totalUsd;
            $order->total_amount_btc = $totalBtc;
            $order->status = 'pending';
    
            Log::info('Order before save', [
                'order' => $order->toArray()
            ]);
    
            $order->save();
    
            Log::info('Order after save', [
                'order' => $order->fresh()->toArray()
            ]);
    
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $itemPriceUsd = $product->getCurrentPriceInUsd($cartItem->quantity) * $cartItem->quantity;
                $itemPriceBtc = $this->currencyConversionService->convertUsdToBtc($itemPriceUsd);
    
                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'price_usd' => $itemPriceUsd,
                    'price_btc' => $itemPriceBtc,
                    'vendor_id' => $product->user_id,
                    'status' => 'pending'
                ]);
                $orderItem->save();
    
                $product->stock -= $cartItem->quantity;
                $product->save();
            }
    
            $wallet->balance_btc -= $totalBtc;
            $wallet->save();
    
            $cart->items()->delete();
            $cart->delete();
    
            DB::commit();
    
            event(new OrderPlaced($order));
    
            return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully.');
        } catch (InsufficientStockException|InsufficientFundsException $e) {
            DB::rollBack();
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('cart.index')->with('error', 'An error occurred while processing your order. Please try again.');
        }
    }
}