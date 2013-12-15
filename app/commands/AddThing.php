<?php
date_default_timezone_set('UTC');

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Artisan CLI extension to create things
 *
 * @license AGPLv3
 * @author Quentin Kaiser <contact@quentinkaiser.be>
 */
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
	protected $description = 'Add thing to the reservations API for a certain organization';

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
		
        // get thing's name
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

        // get thing's type
		do {
			$type = $this->ask("Type (i.e. room) : ");
			if(empty($type))
				$this->comment("Your type is empty");
		} while(empty($type));

        // get thing's description
		do{
			$description = $this->ask("Description : ");
			if(empty($description))
				$this->comment("Your description is empty");
		} while(empty($description));

        // get thing's opening hours
		$opening_hours = array();
        
		$this->info("\t{$name} - schedule");
        $add = $this->ask("\t\tAdd opening days ? Y/n") == "Y" ? 1 : 0;

        while($add) {
        	do {
        		$day = $this->ask("\t\tDay of week : ");
        		if($day < 1 || $day > 7)
        			$this->comment("\t\tYour day must be an integer between 1 and 8.");	
        	} while($day < 1 || $day > 7);

            do {
            	do {
            		$valid_from = strtotime($this->ask("\t\tValid from (d-m-Y) : "));
            		if(empty($valid_from))
            			$this->comment("\t\tYour date is empty");
                    if ($valid_from < time())
                        $this->comment("\t\tYour valid from value is before now");
            	} while(empty($valid_from) || $valid_from < time());

            	do {
            		$valid_through = strtotime($this->ask("\t\tValid through (d-m-Y) : "));
            		if(empty($valid_through))
            			$this->comment("\t\tYour date is empty");
                    if ($valid_through < time())
                        $this->comment("\t\tYour valid through value is before now");
            	} while(empty($valid_through) || $valid_through < time());

                if($valid_from > $valid_through)
                    $this->comment("\t\tYour valid through date is before valid from date.");
            } while($valid_from > $valid_through);

            $opens = array();
            $closes = array();

            $this->info("\t\t\t{$name} - schedule [day {$day} - opening hours]");
            do {
            	do {
                    $valid = true;
            		$open_close = $this->ask("\t\t\t\tOpening / Closing hour (H:m - H:m) : ");
            		$open_close = explode("-", $open_close);

                    if(count($open_close) < 2) {
            			$this->comment("\t\t\t\tYour opening closing hours are not valid");
                        $valid = false;
                    }
                    if(!preg_match("/(2[0-3]|[01][0-9]):[0-5][0-9]/", $open_close[0])) {
                        $this->comment("\t\t\t\tYour opening hour is not valid.");
                        $valid = false;
                    }
                    
                    if(!preg_match("/(2[0-3]|[01][0-9]):[0-5][0-9]/", $open_close[1])) {
                        $this->comment("\t\t\t\tYour closing hour is not valid.");
                        $valid = false;
                    }
                    
            	} while(!$valid);
                
                array_push($opens, $open_close[0]);
                array_push($closes, $open_close[1]);
                $add = $this->ask("\t\t\tAdd opening hours ? Y/n") == "Y" ? 1 : 0;   

            } while($add);

        	array_push(
        		$opening_hours,
        		array(
        			'validFrom' => date('c', $valid_from),
        			'validThrough' => date('c', $valid_through),
        			'dayOfWeek' => $day,
        			'opens' => $opens,
        			'closes' => $closes
        		)
        	);
            $add = $this->ask("\t\tAdd opening day ? Y/n") == "Y" ? 1 : 0;

        }
       
        //get thing's price rates
       	$this->info("\t{$name} - price rates");
       	do {
       		$currency = $this->ask("\t\tCurrency (ISO4217 format) : ");
       		if(empty($currency) || !in_array($currency, $this->ISO4217))
       			$this->comment("\t\tYour currency value is invalid");
       	} while(empty($currency) || !in_array($currency, $this->ISO4217));

        $price_rate = array();
       	$price_rate['currency'] = $currency;
       	$rates = array('hourly', 'daily', 'weekly', 'monthly', 'yearly');
       	do {
       		do {
       		$rate = $this->ask("\t\tRate (hourly, daily, weekly, monthly, yearly) : ");
	       		if(!in_array($rate, $rates))
	       			$this->comment("\t\tYour rate value is invalid");
	       	} while(!in_array($rate, $rates));
	       	do {
	       		$price = $this->ask("\t\tPrice for {$rate} rate in {$currency} : ");
	       		if(empty($price) || $price < 0)
	       			$this->comment("\t\tYour price value is invalid");
	       	} while(empty($price) || $price < 0);

	       	$price_rate[$rate] = $price;
	       	$add = $this->ask("\t\tAdd another price rate ? Y/n : ") == "Y" ? 1 : 0;
       	} while($add);
       	

        //get thing's location 

       	$this->info("\t{$name} - location");
       	
       	do {
       		$building_name = $this->ask("\t\tBuilding name : ");
       		if(empty($building_name))
       			$this->comment("\t\tYour building name value is invalid");
       	} while(empty($building_name));

       	do {
       		$floor = $this->ask("\t\tFloor : ");
       		if(empty($floor))
       			$this->comment("\t\tYour floor value is invalid");
       	} while(empty($floor));

       	

       	$this->info("\t\t\t{$name} - location [map]");

       	do {
       		$img = $this->ask("\t\t\tMap image URL : ");
       		if(empty($img) || !filter_var($img, FILTER_VALIDATE_URL))
       			$this->comment("\t\t\tYour map image URL is invalid");
       	} while(empty($img) || !filter_var($img, FILTER_VALIDATE_URL));

       	do {
       		$reference = $this->ask("\t\t\tMap reference : ");
       		if(empty($reference))
       			$this->comment("\t\t\tYour map reference is invalid");
       	} while(empty($reference));

        

        // get thing's contact and support vcard URLs
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

        //get thing's amenities and fill properties
       	$this->info("\t{$name} amenities");
       	$amenities = Entity::where('type', '=', 'amenity')->where('user_id', '=', $user->id)->get();
       	$add = $this->ask("\t\tAdd amenities ? Y/n") == "Y" ? 1 : 0;
        $_amenities = array();
       	while($add) {
       	    
	       	do{
		       	$this->info("\t\tAvailable amenities : ");
		       	foreach($amenities as $amenity){
		       		$this->info("\t\t\t[{$amenity->id}] {$amenity->name}");
		       	}
		       	$id = $this->ask("\t\tAmenity id : ");
		       	$present = false;
		    	foreach($amenities as $amenity)
		    		if ($amenity->id == $id) $present = true;

		    	if(empty($id) || !$present)
		    		$this->comment("\t\tYour amenity id is invalid");
		    } while(empty($id) || !$present);
		    
		    foreach($amenities as $amenity) {

		       		if($amenity->id == $id) {
                        $this->info("\t\t\t{$amenity->name} properties");
		       			$schema = json_decode($amenity->body);
		       			foreach($schema->properties as $property_name => $property){
		       				do{
		       					$value = $this->ask("\t\t\t\t{$property_name} ({$property->description}) : ");
		       					if(empty($value))
		       						$this->comment("\t\t\t\tYour {$property_name} value is invalid.");
		       				} while(empty($value));
		       				array_push($_amenities, array(Config::get('app.url') . $user->username . '/amenities/' . $property_name => $value));
		       			}
		       			
		       		}
		    }
		    $add = $this->ask("\t\tAdd another amenity ? Y/n") == "Y" ? 1 : 0;
		}

        //create thing and save it in database
        $thing = new Entity;
        $thing->name = $name;
        $thing->type = $type;
        $thing->user_id = $user->id;
        
        // create thing's body
        $body = array();
        $body['name'] = $name;
        $body['type'] = $type;
        $body['description'] = $description;
        $body['opening_hours'] = $opening_hours;
        $body['price'] = $price_rate;
        $body['location'] = array();
        $body['location']['building_name'] = $building_name;
        $body['location']['floor'] = $floor;
        $body['location']['map'] = array();
        $body['location']['map']['img'] = $img;
        $body['location']['map']['reference'] = $reference;
        $body['contact'] = $contact;
        $body['support'] = $support;
        $body['amenities'] = $_amenities;
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