<?php


class ReservationValidator extends Illuminate\Validation\Validator {
	

	public function validateName($attribute, $value, $parameters)
    {
    	return true;
    }


    public function validateType($attribute, $value, $parameters) {
        return true;
    }

    public function validateTime($attribute, $value, $parameters) {

        return true;
    }

    public function validateComment($attribute, $value, $parameters) {

        return true;
    }

    public function validateSubject($attribute, $value, $parameters) {
    	return true;
    }

    public function validateAnnounce($attribute, $value, $parameters) {

        return true;
    }
}


?>