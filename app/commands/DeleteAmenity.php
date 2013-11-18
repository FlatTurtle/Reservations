<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Artisan CLI extension to delete amenities
 *
 * @license AGPLv3
 * @author Quentin Kaiser <contact@quentinkaiser.be>
 */
class DeleteAmenity extends Command {

    /**
     * The console command name
     *
     * @var string
     */
    protected $name = 'reservations:deleteAmenity';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = "Delete an amenity from your database";

    /**
     * Execute the console command
     *
     * @return void
     */
    public function fire(){

        // check if the provided user exists
        $user = User::where('username', '=', $this->option('user'))->first();
        if (!isset($user)) {
            $this->comment("This user don't exist.");
            return;
        }

        // check if the provided amenity exists and delete it
        $amenity = Entity::where('type', '=', 'amenity')
        ->where('name', '=', $this->argument('name'))
        ->where('user_id', '=', $user->id)
        ->first();
        if (isset($amenity)) {
            $amenity->delete();
            $this->info("Amenity '{$amenity->name}' has been deleted.");
        } else {
            $this->comment("This amenity do not exist.");
        }
        
    }

    /**
     * Get the console command arguments
     *
     * @return array
     */
    protected function getArguments(){
        return array(
            array('name', InputArgument::REQUIRED, 'Amenity\'s name'),
        );
    }

    /**
     * Get the console command options
     *
     * @return array
     */
    protected function getOptions(){
        return array(
            array('user', null, InputOption::VALUE_REQUIRED, 'Delete amenity for this user')
        );
    }
}