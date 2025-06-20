<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasExpenseItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    /**
     * Get the kas records associated with this expense item.
     */
    public function kas()
    {
        return $this->hasMany(Kas::class);
    }
}
