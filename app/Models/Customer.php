<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Customer extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_id',
        'cared_by',
    ];

    protected $casts = [
        'cared_by' => 'array',
    ];

    protected $attributes = [
        'cared_by' => '[]',
    ];

    public function getCaredByNamesAttribute(): array
    {
        if (empty($this->cared_by)) {
            return [];
        }

        return User::whereIn('id', $this->cared_by)
            ->pluck('name')
            ->toArray();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
