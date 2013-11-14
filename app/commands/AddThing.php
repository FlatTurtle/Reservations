<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AddThing extends Command {

    /**
     * ISO4217 currencies definition array
     *
     * @var array
     */
    private $ISO4217 = array(
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
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'reservations:addThing';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add Thing to the reservations API for a certain organization';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$user = User::where('username', '=', $this->option('user'))->first();
        if (!isset($user)) {
            $this->comment("This user don't exist.");
            return;
        }
		$thing = new Entity;
		do{
            $valid = true;
			$name = $this->ask("Name : ");
			if(empty($name)) {
				$this->comment("Your name is empty");
                $valid = false;
            }
            if(Entity::where('name', '=', $name)->where('user_id', '=', $user->id)->first() != null) {
                $this->comment("A thing with this name already exist.");
                $valid = false;
            }
		} while(!$valid);

		do {
			$type = $this->ask("Type (i.e. room) : ");
			if(empty($type))
				$this->comment("Your type is empty");
		} while(empty($type));

		do{
			$description = $this->ask("Description : ");
			if(empty($description))
				$this->comment("Your description is empty");
		} while(empty($description));

		$thing->name = $name;
		$thing->type = $type;
        $thing->user_id = $user->id;
		
		$body = array();
		$body['name'] = $name;
		$body['type'] = $type;
		$body['description'] = $description;

		$body['opening_hours'] = array();

		$this->info("\t{$thing->name} - opening hours");
        
        $add = $this->ask("Add opening days ? Y/n") == "Y" ? 1 : 0;

        while($add) {
        	do {
        		$day = $this->ask("Day of week : ");
        		if($day < 1 || $day > 8)
        			$this->comment("Your day must be an integer between 1 and 8.");	
        	} while($day < 1 || $day > 8);

            do {
            	do {
            		$valid_from = strtotime($this->ask("Valid from (d-m-Y) : "));
            		if(empty($valid_from))
            			$this->comment("Your date is empty");
                    if ($valid_from < time())
                        $this->comment("Your valid from value is before now");
            	} while(empty($valid_from) || $valid_from < time());

            	do {
            		$valid_through = strtotime($this->ask("Valid through (d-m-Y) : "));
            		if(empty($valid_through))
            			$this->comment("Your date is empty");
                    if ($valid_through < time())
                        $this->comment("Your valid through value is before now");
            	} while(empty($valid_through) || $valid_through < time());

                if($valid_from > $valid_through)
                    $this->comment("Your valid through date is before valid from date.");
            } while($valid_from > $valid_through);

            $opens = array();
            $closes = array();

            do {
            	do {
                    $valid = true;
            		$open_close = $this->ask("Opening / Closing hour (H:m / H:m) : ");
            		$open_close = explode("/", $open_close);

                    if(count($open_close) < 2) {
            			$this->comment("Your opening closing hours are not valid");
                        $valid = false;
                    }
                    print_r($open_close);
                    if(!preg_match("/(2[0-3]|[01][0-9]):[0-5][0-9]/", $open_close[0])) {
                        $this->comment("Your opening hour is not valid.");
                        $valid = false;
                    }
                    
                    if(!preg_match("/(2[0-3]|[01][0-9]):[0-5][0-9]/", $open_close[1])) {
                        $this->comment("Your closing hour is not valid.");
                        $valid = false;
                    }
                    
            	} while(!$valid);
                
                array_push($opens, $open_close[0]);
                array_push($closes, $open_close[1]);
                $add = $this->ask("Add opening hours ? Y/n") == "Y" ? 1 : 0;   

            } while($add);

        	array_push(
        		$body['opening_hours'],
        		array(
        			'validFrom' => $valid_from,
        			'validThrough' => $valid_through,
        			'dayOfWeek' => $day,
        			'opens' => $opens,
        			'closes' => $closes
        		)
        	);
            $add = $this->ask("Add opening day ? Y/n") == "Y" ? 1 : 0;

        }
       
       	$this->info("\tPrice rates");
       	do {
       		$currency = $this->ask("Currency (ISO4217 format) : ");
       		if(empty($currency) || !in_array($currency, $this->ISO4217))
       			$this->comment("Your currency value is invalid");
       	} while(empty($currency) || !in_array($currency, $this->ISO4217));

       	$body['price'] = array();
       	$body['price']['currency'] = $currency;
       	$rates = array('hourly', 'daily', 'weekly', 'monthly', 'yearly');
       	do {
       		do {
       		$rate = $this->ask("Rate (hourly, daily, weekly, monthly, yearly) : ");
	       		if(!in_array($rate, $rates))
	       			$this->comment("Your rate value is invalid");
	       	} while(!in_array($rate, $rates));
	       	do {
	       		$price = $this->ask("Price for {$rate} rate in {$currency} : ");
	       		if(empty($price) || $price < 0)
	       			$this->comment("Your price value is invalid");
	       	} while(empty($price) || $price < 0);

	       	$body['price'][$rate] = $price;
	       	$add = $this->ask("Add another price rate ? Y/n : ") == "Y" ? 1 : 0;
       	} while($add);
       	

       	$this->info("\t{$name} location");

       	$body['location'] = array();
       	do {
       		$building_name = $this->ask("Building name : ");
       		if(empty($building_name))
       			$this->comment("Your building name value is invalid");
       	} while(empty($building_name));

       	do {
       		$floor = $this->ask("Floor : ");
       		if(empty($floor))
       			$this->comment("Your floor value is invalid");
       	} while(empty($floor));

       	$body['location']['building_name'] = $building_name;
       	$body['location']['floor'] = $floor;

       	$this->info("\t{$name} location - map");
       	do {
       		$img = $this->ask("Map image URL : ");
       		if(empty($img) || !filter_var($img, FILTER_VALIDATE_URL))
       			$this->comment("Your map image URL is invalid");
       	} while(empty($img) || !filter_var($img, FILTER_VALIDATE_URL));

       	do {
       		$reference = $this->ask("Map reference : ");
       		if(empty($reference))
       			$this->comment("Your map reference is invalid");
       	} while(empty($reference));

       	$body['location']['map'] = array();
       	$body['location']['map']['img'] = $img;
       	$body['location']['map']['reference'] = $reference;

       	do {
       		$contact = $this->ask("Contact vcard URL : ");
       		if(empty($contact) || !filter_var($contact, FILTER_VALIDATE_URL))
       			$this->comment("Your contact vcard URL is invalid");
       	} while(empty($contact) || !filter_var($contact, FILTER_VALIDATE_URL));

       	do {
       		$support = $this->ask("Support vcard URL : ");
       		if(empty($support) || !filter_var($support, FILTER_VALIDATE_URL))
       			$this->comment("Your support vcard URL is invalid");
       	} while(empty($support) || !filter_var($support, FILTER_VALIDATE_URL));

       	$body['amenities'] = array();
       	$this->info("\t{$name} amenities");
       	$amenities = Entity::where('type', '=', 'amenity')->where('user_id', '=', $user->id)->get();
       	$add = $this->ask("Add amenities ? Y/n") == "Y" ? 1 : 0;

       	while($add) {
       	
	       	do{
		       	$this->info("Available amenities : ");
		       	foreach($amenities as $amenity){
		       		$this->info("[{$amenity->id}] {$amenity->name}");
		       	}
		       	$id = $this->ask("Amenity id : ");
		       	$present = false;
		    	foreach($amenities as $amenity)
		    		if ($amenity->id == $id) $present = true;

		    	if(empty($id) || !$present)
		    		$this->comment("Your amenity id is invalid");
		    } while(empty($id) || !$present);
		    
		    foreach($amenities as $amenity) {

		       		if($amenity->id == $id) {

		       			$schema = json_decode($amenity->body);
		       			foreach($schema->properties as $name => $property){
		       				do{
		       					$value = $this->ask("{$name} ({$property->description}) : ");
		       					if(empty($value))
		       						$this->comment("Your {$name} value is invalid.");
		       				} while(empty($value));
		       				$body['amenities'][Config::get('app.url') . $user->username . '/amenities/' . $name] = $value;
		       			}
		       			
		       		}
		    }
		    $add = $this->ask("Add another amenity ? Y/n") == "Y" ? 1 : 0;
		}
		$thing->body = json_encode($body);
        $thing->save();
        $this->info("Thing successfully saved");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('user', null, InputOption::VALUE_OPTIONAL, 'Add thing for this user.', null)
		);
	}

}