<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Auth\UserInterface;
/**
 * The 
 *
 * @license AGPLv3
 * @author Pieter Colpaert
 */
class DeleteUser extends Command {

    /**
     * The console command name
     *
     * @var string
     */
    protected $name = 'reservations:deleteUser';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = "Delete a user from your database";

    /**
     * Execute the console command
     *
     * @return void
     */
    public function fire(){

        $username = $this->argument('username');
        $user = User::where('username', '=', $username)->first();
        if(isset($user)){
            $user->delete();
            $this->info("User '{$user->username}' has been deleted.");
        }else{
            $this->info("User '{$username}' do not exist.");
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
        );
    }

    /**
     * Get the console command options
     *
     * @return array
     */
    protected function getOptions(){
        return array(
        );
    }
}