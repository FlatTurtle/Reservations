<?php

class Reservation extends Eloquent {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'reservation';

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
	 * The customer that made this reservation.
	 * @var Customer
	 */
	private $customer;

	public function customer() {
		return $this->hasOne('Customer');
	}


	/**
	 * The entity that is reserved.
	 * @var Entity
	 */
	private $entity;

	public function entity() {
		return $this->hasOne('entity');
	}



}




?>