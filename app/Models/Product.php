<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Product extends Model
{
    protected $fillable = [
        'name', 'price', 'stock', 
        'trade_offer_min_qty', 'trade_offer_get_qty', 
        'discount', 'discount_or_trade_offer_start_date', 
        'discount_or_trade_offer_end_date'
    ];

    protected $dates = [
        'discount_or_trade_offer_start_date',
        'discount_or_trade_offer_end_date'
    ];

    public function isOfferActive()
    {
        $now = Carbon::now();
        return $this->discount_or_trade_offer_start_date && 
               $this->discount_or_trade_offer_end_date &&
               $now->between(
                   $this->discount_or_trade_offer_start_date, 
                   $this->discount_or_trade_offer_end_date
               );
    }
}