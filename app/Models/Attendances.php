<?php 
    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    
    use App\Traits\BelongsToCompany;

    class Attendances extends Model
    {
        use HasFactory, BelongsToCompany;

        protected $fillable = [
            'user_id',
            'date',
            'check_in',
            'check_out',
            'method',
            'status',
            'remarks',
        ];

        public function user()
        {
            return $this->belongsTo(\App\Models\User::class, 'user_id');
        }
    }

?>
