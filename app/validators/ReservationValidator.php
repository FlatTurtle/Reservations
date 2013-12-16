<?php
class ReservationValidator {

     
      
      /**
       * Check if a date is a valid ISO8601 formatted date.
       * @param $date : the date to check
       */
      private function isValidISO8601($date) {
          return preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $date) > 0 ;
      }

      public function validateTime($attribute, $value, $parameters)
      {
          if(!isset($value['from']) || !isset($value['to']))
              return false;
          //check against ISO8601 regex
          if(!$this->isValidISO8601($value['from']))
              return false;
          if(!$this->isValidISO8601($value['to']))
              return false;
          $from=strtotime($value['from']);
          $to=strtotime($value['to']);
          $now = time();
          $span = (int) Config::get('app.reservation_time_span');

          if(!$from || !$to)
              return false;
          if($from < $now - $span)
              return false;
          if ($to < $now - $span)
              return false;
          if ($to < $from)
              return false;
          if (($to-$from) < $span)
              return false;
          return true;
      }

      public function validateCustomer($attribute, $value, $parameters) {

        $customer_validator = Validator::make(
                $value,
                array(
                  'email' => 'required|email',
                  'company' => 'required'
                )
        );
        if($customer_validator->fails())
          return false;
        else
          return true;

      }

    }
