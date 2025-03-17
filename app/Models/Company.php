<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Company",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="cnpj", type="string"),
 *     @OA\Property(property="email", type="string")
 * )
 */

class Company extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'cnpj',
        'email'
    ];

    protected $table = 'company';

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
