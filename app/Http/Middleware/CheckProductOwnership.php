<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckProductOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $product = $request->route('product');

        if (auth()->user()->isAdmin() || auth()->id() === $product->user_id) {
            return $next($request);
        }

        return redirect()->route('products.show', $product)->with('error', 'You do not have permission to edit this product.');
    }
}