<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportConversation extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'customer_name',
        'customer_email',
        'status',
        'source',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function unreadCount(): int
    {
        return $this->messages()->whereNull('read_at')->count();
    }
}
