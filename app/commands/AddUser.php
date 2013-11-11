<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
/**
 * The 
 *
 * @license AGPLv3
 * @author Pieter Colpaert
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