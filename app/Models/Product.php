<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Product",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="qty", type="integer"),
 *     @OA\Property(property="qty_min", type="integer"),
 *     @OA\Property(property="company_id", type="integer")
 * )
 */

class Product extends Model
{
  use HasFactory;
  public $timestamps = false;

  protected $fillable = [
    'name',
    'qty',
    'qty_min',
    'company_id',
  ];

  protected $table = 'products';

  public function company()
  {
    return $this->belongsTo(Company::class);
  }
}
