<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Artisan CLI extension to delete things
 *
 * @license AGPLv3
 * @author Quentin Kaiser <contact@quentinkaiser.be>
 */
class DeleteThing extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'reservations:deleteThing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Thing from the reservations API for a certain organization';

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
        // check if the provided user exists
        $user = User::where('username', '=', $this->option('user'))->first();
        if (!isset($user)) {
            $this->comment("This user don't exist.");
            return;
        }

        // check if the provided thing exists and delete it
        $thing = Entity::where('type', '!=', 'amenity')
        ->where('name', '=', $this->argument('name'))
        ->where('user_id', '=', $user->id)
        ->first();
        if (isset($thing)) {
            $thing->delete();
            $this->info("Thing '{$thing->name}' has been deleted.");
        } else {
            $this->comment("This thing do not exist.");
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'The thing name.')
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
            array('user', null, InputOption::VALUE_OPTIONAL, 'The owner of the thing.', null)
        );
    }

}