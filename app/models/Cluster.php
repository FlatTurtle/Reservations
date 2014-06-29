<?php

use Illuminate\Auth\UserInterface;

class Cluster extends Eloquent implements UserInterface {


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cluster';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password');
    protected $fillable = array('clustername');

    public function reservations() {
        return $this->hasMany('Reservation');
    }

    public function entities() {
        return $this->hasMany('Entity');
    }

    public function companies() {
        return $this->hasMany('Company');
    }

    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * Get the unique identifier for the cluster.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the cluster.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }
    
    public function isAdmin() {
        return $this->user()->first()->isAdmin();
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

