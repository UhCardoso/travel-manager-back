<?php

namespace App\Models;

use App\Enums\TravelRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'travel_requests';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'country',
        'town',
        'state',
        'region',
        'departure_date',
        'return_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'status' => TravelRequestStatus::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'departure_date',
        'return_date',
    ];

    /**
     * Get the user that owns the travel request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
