<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) FusionInvoice, LLC <jessedterry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Payments\Models;

use FI\Classes\Date;
use FI\Classes\CurrencyFormatter;
use FI\Classes\NumberFormatter;

class Payment extends \Eloquent {

    /**
     * Guarded properties
     * @var array
     */
	protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
   
    public function custom()
    {
        return $this->hasOne('FI\Modules\CustomFields\Models\PaymentCustom');
    }

    public function invoice()
    {
    	return $this->belongsTo('FI\Modules\Invoices\Models\Invoice');
    }

    public function paymentMethod()
    {
    	return $this->belongsTo('FI\Modules\PaymentMethods\Models\PaymentMethod');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedPaidAtAttribute()
    {
    	return Date::format($this->attributes['paid_at']);
    }

    public function getFormattedAmountAttribute()
    {
    	return CurrencyFormatter::format($this->attributes['amount']);
    }

    public function getFormattedNumericAmountAttribute()
    {
        return NumberFormatter::format($this->attributes['amount']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
   
    public function scopeByDateRange($query, $from, $to)
    {
        return $query->where('paid_at', '>=', $from)->where('paid_at', '<=', $to);
    }

    public function scopeByYear($query, $year)
    {
        return $query->where(\DB::raw('YEAR(paid_at)'), '=', $year);
    }

    public function scopeKeywords($query, $keywords)
    {
        $keywords = explode(' ', $keywords);

        foreach ($keywords as $keyword)
        {
            if ($keyword)
            {
                $keyword = strtolower($keyword);

                $query->whereRaw('(created_at like ? 
                    or invoice_id in (select invoice_id from invoices where number like ? or client_id in (select id from clients where lower(name) like ?))
                    or payment_method_id in (select id from payment_methods where lower(name) like ?))', array("%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%"));
            }
        }

        return $query;
    }

}