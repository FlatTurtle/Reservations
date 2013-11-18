<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Artisan CLI extension to create amenities 
 *
 * @license AGPLv3
 * @author Quentin Kaiser <contact@quentinkaiser.be>
 */
class AddAmenity extends Command {

    /**
     * The console command name
     *
     * @var string
     */
    protected $name = 'reservations:addAmenity';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = "Add an amenity to your database";

    /**
     * Execute the console command
     *
     * @return void
     */
    public function fire(){
        
        $allowed_types = array('array', 'boolean', 'integer', 'number', 'null', 'object', 'string');

        $user = User::where('username', '=', $this->option('user'))->first();
        if (!isset($user)) {
            $this->comment("This user don't exist.");
            return;
        }

        do{
            // get amenity's name
            do {
                $name = $this->ask("Amenity name : ");
                if(empty($name))
                    $this->comment("Your amenity name is empty.");
            } while(empty($name));

            // check if an amenity with this name and user already exist
            $amenity = Entity::where('type', '=', 'amenity')->where('name', '=', $name)->first();
            if(isset($amenity))
                $this->comment("An amenity with this name already exists, please choose another one.");
        }while(isset($amenity));
        
        // get amenity's title
        do{
            $title = $this->ask("Amenity title : ");
            if(empty($title))
                $this->comment("Your amenity title is empty.");
        } while(empty($title));

        // get amenity's description
        do{
            $description = $this->ask("Amenity description : ");
            if(empty($description))
                $this->comment("Your amenity description is empty.");
        } while(empty($description));

        $amenity = new Entity;
        $amenity->user_id = $user->id;
        $amenity->type = "amenity";
        $amenity->name = $name;

        $json_schema = array();
        $json_schema['$schema'] 
            = "http://json-schema.org/draft-04/schema#";
        $json_schema['title'] = $title;
        $json_schema['description'] = $description;
        $json_schema['type'] = 'object';
        $json_schema['properties'] = array();
        $i = 0;

        $this->info("\n\n{$amenity->name} properties.");
        do{
            $this->comment("\n# Property {$i}");

            // get property name
            do{
                $name = $this->ask("\tProperty name : ");
                if(in_array($name, array_keys($json_schema['properties'])))
                    $this->comment("A property with this name already exist.");
                if(empty($name))
                    $this->comment("Your property name is empty.");
            } while(in_array($name, array_keys($json_schema['properties'])) || empty($name));

            // get property description
            do{
                $description = $this->ask("\tProperty description : ");
                if(empty($description))
                    $this->comment("Your property description is empty.");
            } while(empty($description));
            
            // set property name and description
            $json_schema['properties'][$name] = array();
            $json_schema['properties'][$name]['description'] = $description;

            // get property type and verify if it is a valid json-schema core type
            do {
                $type = $this->ask("\tProperty type (array, boolean, integer, number, null, object, string) : ");
                if(!in_array($type, $allowed_types))
                    $this->comment("The property type you provided is not valid. Valid types are 'array', 'boolean', 'integer', 'number', 'null', 'object', 'string'.");
            } while(!in_array($type, $allowed_types));

            // set property type
            $json_schema['properties'][$name]['type'] = $type;

            $stop = $this->ask("\nDo you want to add another property ? [Y]/n") == "Y" ? 0 : 1;
            $i++;
        }while(!$stop);

        $amenity->body = json_encode($json_schema);
        $amenity->save();
        $this->info("Amenity '{$amenity->name}' has been saved.");
        return;
    }

    /**
     * Get the console command arguments
     *
     * @return array
     */
    protected function getArguments(){
        return array(
        );
    }

    /**
     * Get the console command options
     *
     * @return array
     */
    protected function getOptions(){
        return array(
            array('user', null, InputOption::VALUE_REQUIRED, 'Add amenity for this user')
        );
    }
}