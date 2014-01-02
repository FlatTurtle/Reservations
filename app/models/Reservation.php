<?php

class Reservation extends Eloquent {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reservation';

    protected $fillable = array('id', 'from', 'to', 'subject', 'comment', 'announce', 'customer', 'user_id', 'entity_id','activated');


    /**
     * The customer that made this reservation.
     * @var Customer
     */
    private $user;

    public function user() {
        return $this->hasOne('User');
    }

    /**
     * The entity that is reserved.
     * @var Entity
     */
    private $entity;

    public function entity() {
        return $this->hasOne('entity');
    }

    public function getDates()
    {
        return array('created_at', 'updated_at', 'from', 'to');
    }

    public function scopeActivatedOrBlocking($query){
        // create inactive reservation timeframe
        $end = new DateTime();
        $start = clone $end;
        // set to {activation_timeframe} minutes ago
        $start->sub(new DateInterval("PT". Config::get('app.activation_timeframe') . "M"));

        // wrapped in innerquery to avoid problems with precedence between AND and OR
        return $query->where(function($query) use($start,$end)
        {
            $query->where('activated', '=', true)
                ->orWhereBetween('created_at', array($start, $end));
        });
    }
}