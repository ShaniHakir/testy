<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
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

                // Return the funds to the user's wallet
                $buyer = User::find($order->user_id);
                $buyer->wallet->balance_btc += $item->price * $item->quantity;
                $buyer->wallet->save();

                // Return the stock to the product
                $product = $item->product;
                $product->stock += $item->quantity;
                $product->save();
            }

            if ($order->allItemsHaveStatus('rejected')) {
                $order->status = Order::STATUS_REJECTED;
            } elseif (!$order->orderItems()->where('status', 'pending')->exists()) {
                $order->status = Order::STATUS_PROCESSING;
            }
            $order->save();
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
            
            foreach ($orderItems as $item) {
                $item->status = 'delivered';
                $item->save();

                // Transfer the funds to the vendor's wallet
                $user->wallet->balance_btc += $item->price * $item->quantity;
                $user->wallet->save();
            }

            if ($order->allItemsHaveStatus('delivered')) {
                $order->status = Order::STATUS_COMPLETED;
                $order->save();
            }
        });

        return redirect()->route('orders.index')->with('success', 'Order marked as completed successfully.');
    }
}