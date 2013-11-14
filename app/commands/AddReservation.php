<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AddReservation extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'reservations:addReservation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a reservation to the reservations API for a certain organization';

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
        $reservation = new Reservation;

        $things = Entity::where('type', '!=', 'amenity')->where('user_id', '=', $user->id)->get();
        foreach($things as $thing) {
            $this->info("{$thing->name} [{$thing->id}]");
        }
        do {
            $present = false;
            $thing_id = $this->ask("Thing id : ");
            
            if(empty($thing_id))
                $this->comment("Your thing id is invalid");
            else {
                foreach($things as $thing) {
                    if ($thing->id == $thing_id)
                        $present = true;
                }
                if(!$present)
                    $this->comment("Your thing id is invalid");
            }
        
        } while(empty($thing_id) || !$present);

        do {
            $subject = $this->ask("Subject : ");
            if(empty($subject))
                $this->comment("Your subject is invalid");
        } while(empty($subject));

        do {
            $comment = $this->ask("Comment : ");
            if(empty($comment))
                $this->comment("Your comment is invalid.");
        } while(empty($comment));

        do {
            $valid = true;
            $from = strtotime($this->ask("From (d-m-Y H:m) : "));
            if(empty($from)) {
                $valid = false;
                $this->comment("Your value is empty.");    
            }
            if($from < time()) {
                $valid = false;
                $this->comment("Your reservation can't start before now.");
            }
        } while(!$valid);
        
        do {
            $valid = true;
            $to = strtotime($this->ask("To (d-m-Y H:m) : "));
            if(empty($to)) {
                $valid = false;
                $this->comment("Your value is empty.");    
            }
            if($to < $from) {
                $valid = false;
                $this->comment("Your reservation can't end before it start.");
            }
        } while(!$valid);
        
        $announce = $this->ask("Announce (names separated by a comma) : ");
        if(($announce = explode(",", $announce)) != null)
            $reservation->announce = json_encode($announce);

        $reservation->user_id = $user->id;
        $reservation->entity_id = $thing_id;
        $reservation->subject = $subject;
        $reservation->comment = $comment;
        $reservation->from = date('c', $from);
        $reservation->to = date('c', $to);
        $reservation->save();
        $this->info("Reservation successfully saved");
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
            array('user', null, InputOption::VALUE_OPTIONAL, 'Add reservation for this user.', null)
        );
    }

}