<?php

use Illuminate\Auth\UserInterface;

class User extends Eloquent implements UserInterface {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password');
    protected $fillable = array('username');

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

    public function getRights()
    {
        return $this->rights;
    }

    public function isAdmin()
    {
        return $this->rights==100;
    }

    /**
     * Session stuff
     */
    public function getRememberToken()
    {
            return $this->remember_token;
    }

    public function setRememberToken($value)
    {
            $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
            return 'remember_token';
    }
	
}

