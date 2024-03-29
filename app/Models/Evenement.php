<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evenement extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'evenements';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_nom',
        'event_code',
        'event_couple_nom',
        'event_date_heure',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'event_created_At'=>'datetime:d/m/Y H:i',
        'event_date_heure'=>'datetime:d/m/Y H:i'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'event_created_At',
        'event_date_heure',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Evenement tables des invites
     * @return HasMany
    */
    public function tables():HasMany
    {
        return $this->hasMany(Table::class, foreignKey: 'event_id', localKey: 'id');
    }
}
