<?php


class AmenityValidator extends Illuminate\Validation\Validator {
	

	public function validateName($attribute, $value, $parameters)
    {
        //validate name 
    	return true;
    }


    public function validateDescription($attribute, $value, $parameters) {

         //validate description
        return true;
    }

    public function validateSchema($attribute, $value, $parameters) {

        //verify json
        return true;
    }
}


?>