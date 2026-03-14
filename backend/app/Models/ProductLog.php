<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['product_id','user_id','action','changes','logged_at'];
    protected $casts = ['changes'=>'array','logged_at'=>'datetime'];
    public function user() { return $this->belongsTo(User::class); }
}
