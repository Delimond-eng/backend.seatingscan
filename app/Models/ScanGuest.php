<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanGuest extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'scan_guests';

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
        'event_id',
        'invite_id',
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
        'scan_created_At'=>'datetime:d/m/Y H:i'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'scan_created_At'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Appartenance à l'Evenement
     * @return BelongsTo
     */
    public function event():BelongsTo
    {
        return $this->belongsTo(Evenement::class, foreignKey: 'event_id');
    }

    /**
     * Appartenance à un invité
     * @return BelongsTo
    */
    public function invite():BelongsTo
    {
        return $this->belongsTo(Invite::class, foreignKey: 'invite_id');
    }

}
