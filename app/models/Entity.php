<?php

/**
 * An entity is a generic thing that can be reserved.
 * 
 */
class Entity extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'entity';

	protected $fillable = array('id', 'name', 'type', 'body', 'user_id');
	/**
	 * Simple primary key
	 * @var int
	 */
	private $id;

	/**
	 * Name of the entity.
	 * @var string
	 */
	private $name;

	/**
	 * Type describe the entity (room, amenity, ...).
	 * @var string
	 */
	private $type;

	/**
	 * Body is a json string stored as a blob, describing the entity.
	 * @var string
	 */
	private $body;
	
	/**
	 * User is the entity's owner.
	 * @var User
	 */
	private $user;


	public function user() {
		return $this->belongsTo('User');
	}

}
