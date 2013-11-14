<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Artisan CLI extension to delete reservations
 *
 * @license AGPLv3
 * @author Quentin Kaiser <contact@quentinkaiser.be>
 */
class DeleteReservation extends Command {

    /**
     * The console command name
     *
     * @var string
     */
    protected $name = 'reservations:deleteReservation';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = "Delete a reservation from your database";

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

        // check if the provided reservation exists and delete it
        $reservation = Reservation::where('id', '=', $this->argument('id'))
        ->where('user_id', '=', $user->id)
        ->first();
        if (isset($reservation)) {
            $reservation->delete();
            $this->info("Reservation '{$reservation->id}' has been deleted.");
        } else {
            $this->comment("This reservation do not exist.");
        }        
        return;
    }

    /**
     * Get the console command arguments
     *
     * @return array
     */
    protected function getArguments(){
        return array(
            array('id', InputArgument::REQUIRED, 'Reservation\'s id'),
        );
    }

    /**
     * Get the console command options
     *
     * @return array
     */
    protected function getOptions(){
        return array(
            array('user', null, InputOption::VALUE_REQUIRED, 'Delete reservation for this user')
        );
    }
}