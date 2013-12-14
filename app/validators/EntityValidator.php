<?php


class EntityValidator {

    /**
     * This function throw an error 400 and provide error messages
     * from validator $validator in JSON.
     * @param $validator : a Laravel validator
     */
    private function sendErrorMessage($validator){

        $messages = $validator->messages();
        $s = "JSON does not validate. Violations:\n";
        foreach ($messages->all() as $message) {
            $s .= "$message\n";
        }
        App::abort(400, $s);
    }

    public function validateHours($attribute, $value, $parameters)
    {
        foreach($value as $hour) {
        //validate 24 hours format time
            if(!preg_match("/(2[0-3]|[01][0-9]):[0-5][0-9]/", $hour))
                return false;
        }
        return true;
    }

    public function validateOpeningHours($attribute, $value, $parameters)
    {
        $now = date("Y-m-d h:m:s", time());
        foreach ($value as $opening_hour) {
            $opening_hour_validator = Validator::make(
                $opening_hour,
                array(
                    'opens' => 'required|hours',
                    'closes' => 'required|hours',
                    'validFrom' => 'required',
                    'validThrough' => 'required|after:'.$now,
                    'dayOfWeek' => 'required|numeric|between:0,7'
                )
            );
            if ($opening_hour_validator->fails())
                $this->sendErrorMessage($opening_hour_validator);
        }
        return true;
    }

    public function validatePrice($attribute, $value, $parameters)
    {
        /* we verify that the price object has at least 
          one defined time rate and that the currency is one of the
          ISO4217 standard */

        $timings = array(
          'hourly',
          'daily',
          'weekly',
          'monthly',
          'yearly'
        );
        $ISO4217 = array(
          "AED","AFN","ALL","AMD","ANG","AOA","ARS","AUD","AWG",
          "AZN","BAM","BBD","BDT","BGN","BHD","BIF","BMD","BND",
          "BOB","BOV","BRL","BSD","BTN","BWP","BYR","BZD","CAD",
          "CDF","CHE","CHF","CHW","CLF","CLP","CNY","COP","COU",
          "CRC","CUC","CUP","CVE","CZK","DJF","DKK","DOP","DZD",
          "EGP","ERN","ETB","EUR","FJD","FKP","GBP","GEL","GHS",
          "GIP","GMD","GNF","GTQ","GYD","HKD","HNL","HRK","HTG",
          "HUF","IDR","ILS","INR","IQD","IRR","ISK","JMD","JOD",
          "JPY","KES","KGS","KHR","KMF","KPW","KRW","KWD","KYD",
          "KZT","LAK","LBP","LKR","LRD","LSL","LTL","LVL","LYD",
          "MAD","MDL","MGA","MKD","MMK","MNT","MOP","MRO","MUR",
          "MVR","MWK","MXN","MXV","MYR","MZN","NAD","NGN","NIO",
          "NOK","NPR","NZD","OMR","PAB","PEN","PGK","PHP","PKR",
          "PLN","PYG","QAR","RON","RSD","RUB","RWF","SAR","SBD",
          "SCR","SDG","SEK","SGD","SHP","SLL","SOS","SRD","SSP",
          "STD","SVC","SYP","SZL","THB","TJS","TMT","TND","TOP",
          "TRY","TTD","TWD","TZS","UAH","UGX","USD","USN","USS",
          "UYI","UYU","UZS","VEF","VND","VUV","WST","XAF","XAG",
          "XAU","XBA","XBB","XBC","XBD","XCD","XDR","XFU","XOF",
          "XPD","XPF","XPT","XSU","XTS","XUA","XXX","YER","ZAR",
          "ZMW","ZWL"
        );

        $intersect = array_intersect($timings, array_keys($value));
        foreach ($intersect as $index) {
            if ($value[$index] < 0)
                return false;
        }
        return (
          isset($value['currency']) && 
          in_array($value['currency'], $ISO4217) && 
          !empty($intersect)
        );
    }

    public function validateMap($attribute, $value, $parameters)
    {
        $map_validator = Validator::make(
            $value,
            array(
                'img' => 'required|url',
                'reference' => 'required'
            )
        );
        if ($map_validator->fails())
            $this->sendErrorMessage($map_validator);
        return true;
    }

    public function validateLocation ($attribute, $value, $parameters)
    {
        $location_validator = Validator::make(
            $value,
            array(
                'map' => 'required|map',
                'floor' => 'required|numeric',
                'building_name' => 'required'
            )
        );
        if ($location_validator->fails())
            $this->sendErrorMessage($location_validator);
        return true;
    }

    public function validateAmenities($attribute, $value, $parameters)
    {
        $present = true;
        if (count($value)) {
            /* we check if amenities provided as input
             exists in database */
            $amenities 
                = DB::table('entity')
                ->where('type', '=', 'amenity')
                ->lists('name');
            foreach ($value as $amenity) {
                $present = in_array($amenity, $amenities);
            }
        }
        return $present;
    }

    public function validateBody($attribute, $value, $parameters)
    {
        $body_validator = Validator::make(
            $value,
            array(
                'type' => 'required',
                'description' => 'required',
                'location' => 'required|location',
                'price' => 'required|price',
                'amenities' => 'amenities',
                'contact' => 'required|url',
                'support' => 'required|url',
                'opening_hours' => 'required|opening_hours'
            )
        );
        if($body_validator->fails())
            $this->sendErrorMessage($body_validator);
        return true;
    }
}