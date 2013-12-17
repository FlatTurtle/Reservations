<?php

class Reservation extends Eloquent {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reservation';

    protected $fillable = array('id', 'from', 'to', 'subject', 'comment', 'announce', 'customer', 'user_id', 'entity_id');
    /**
     * Simple primary key
     * @var int
     */
    private $id;


    /**
     * The reservation start on $from.
     * @var timestamp (int)
     */
    private $from;


    /**
     * The reservation end on $to.
     * @var timestamp (int)
     */
    private $to;

    /**
     * Customer subject
     * @var string
     */
    private $subject;


    /**
     * Customer comment.
     * @var string
     */
    private $comment;


    /**
     * For on screen announcements
     * @var array
     */
    private $announce;

    /**
     * Customer that made the reservation. Needed for billing
     *
     */
    private $customer;


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
}