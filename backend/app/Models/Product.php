<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','description','sale_price','cost_price','category','is_active','created_by','updated_by'];
    protected $casts = ['sale_price'=>'decimal:2','cost_price'=>'decimal:2','is_active'=>'boolean'];
    public function images() { return $this->hasMany(ProductImage::class); }
    public function logs()   { return $this->hasMany(ProductLog::class)->latest('logged_at'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
    public function updater(){ return $this->belongsTo(User::class, 'updated_by'); }
}
