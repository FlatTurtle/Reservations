<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Auth\UserInterface;
/**
 * Artisan CLI extension to create users
 *
 * @license AGPLv3
 * @author Quentin Kaiser <contact@quentinkaiser.be>
 */
class AddUser extends Command {

    /**
     * The console command name
     *
     * @var string
     */
    protected $name = 'reservations:addUser';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = "Add a user to your database";

    /**
     * Execute the console command
     *
     * @return void
     */
    public function fire(){

        $hasher = new PasswordHash(8,false);
        $password = $hasher->HashPassword($this->argument('password'));
        $rights = $this->option('admin') != null ? 100 : 0;
        // check if the provided user exists
        $user = User::where('username', '=', $this->argument('username'))->first();
        if(isset($user)){
            // user exists, let's update it
            $user->password = $password;
            $user->rights = $rights;
            $user->save();
            $this->info("User '{$user->username}' has been updated.");
        }else{
            // user do not exists, let's create it
            $user = new User;
            $user->username = $this->argument('username');
            $user->password = $password;
            $user->rights = $rights;
            $user->save();
            $this->info("User '{$user->username}' has been updated.");
        }
        
    }

    /**
     * Get the console command arguments
     *
     * @return array
     */
    protected function getArguments(){
        return array(
            array('username', InputArgument::REQUIRED, 'Full name of the user'),
            array('password', InputArgument::REQUIRED, 'Clear password of the user')
        );
    }

    /**
     * Get the console command options
     *
     * @return array
     */
    protected function getOptions(){
        return array(
            array('admin', null, InputOption::VALUE_NONE, 'Add user to administrators')
        );
    }
}