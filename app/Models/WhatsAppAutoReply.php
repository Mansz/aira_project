<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppAutoReply extends Model
{
    protected $table = 'whatsapp_auto_replies';
    
    protected $fillable = [
        'keyword',
        'response',
        'is_regex',
        'is_active'
    ];

    protected $casts = [
        'is_regex' => 'boolean',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'is_regex' => false,
        'is_active' => true
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function matches($message)
    {
        if ($this->is_regex) {
            return preg_match($this->keyword, $message);
        }
        
        return stripos($message, $this->keyword) !== false;
    }
}
