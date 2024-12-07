<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'exists:products,id',
            'items.*.quantity' => 'integer|min:1'
        ]);

        return DB::transaction(function () use ($request) {
            $totalPrice = 0;
            $totalDiscount = 0;
            $updatedProducts = [];

            $sale = Sale::create([
                'total_price' => 0,
                'total_discount' => 0
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = $item['quantity'];

                // Stock validation
                if ($product->stock < $quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                // Price calculation with discounts and trade offers
                $unitPrice = $product->price;
                $discountAmount = 0;

                // Check for active discounts
                if ($product->isOfferActive()) {
                    if ($product->discount) {
                        // Percentage discount
                        $discountAmount = $unitPrice * ($product->discount / 100);
                        $unitPrice -= $discountAmount;
                    }

                    // Trade offer logic (e.g., Buy 3 Get 1 Free)
                    if ($product->trade_offer_min_qty && $product->trade_offer_get_qty) {
                        $freeItems = floor($quantity / $product->trade_offer_min_qty);
                        $totalPrice += $unitPrice * ($quantity - $freeItems);
                    } else {
                        $totalPrice += $unitPrice * $quantity;
                    }
                } else {
                    $totalPrice += $unitPrice * $quantity;
                }

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount * $quantity
                ]);

                // Update product stock
                $product->decrement('stock', $quantity);
                $updatedProducts[] = $product;

                $totalDiscount += $discountAmount * $quantity;
            }

            // Update sale with total prices
            $sale->update([
                'total_price' => $totalPrice,
                'total_discount' => $totalDiscount
            ]);

            return response()->json([
                'message' => 'Sale processed successfully',
                'sale' => $sale,
                'updated_products' => $updatedProducts
            ]);
        });
    }
}