<?php

use Illuminate\Auth\UserInterface;

class Customer extends Eloquent implements UserInterface {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'customer';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password');

	public function reservations() {
		return $this->hasMany('Reservation');
	}

	public function entities() {
		return $this->hasMany('Entity');
	}
	
	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}
	
}



?>