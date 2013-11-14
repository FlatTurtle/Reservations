<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Artisan CLI extension to create reservations
 *
 * @license AGPLv3
 * @author Quentin Kaiser <contact@quentinkaiser.be>
 */
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
     * Verify if a room is available by checking its $opening_hours
     * agains the $reservation_time.
     * @param opening_hours : the room's opening hours
     * @param reservation_time : the reservation's time (from & to)
     */
    private function isAvailable($opening_hours, $reservation_time)
    {

        $from = $reservation_time['from'];
        $to = $reservation_time['to'];
        $available = false;

        foreach ($opening_hours as $opening_hour) {
            if ($from < $opening_hour->validFrom) {
                return false;
            }
            if ($to > $opening_hour->validThrough) {
                return false;
            }

            /* 
             * We do not support reservation that goes on multiple days,
             * if a user wants to book an entity on multiple days he had to
             * do reservations for each day
             */

            //compare dayOfWeek with the day value of $from and $to
            if ($opening_hour->dayOfWeek == date('N', $from)
                && $opening_hour->dayOfWeek == date('N', $to)
            ) {
                $i=0;
                foreach (array_combine($opening_hour->opens, $opening_hour->closes) 
                  as $open => $close) {
                    /* open an close values are formatted as H:m and dayOfWeek 
                        is the same so we compare timestamp between $from, $to,
                        $open and $close and the same day. */
                    if (strtotime(date('Y-m-d H:m', $from)) >= strtotime(date('Y-m-d', $from) . $open))
                        $i++;
                    if (strtotime(date('Y-m-d H:m', $from)) < strtotime(date('Y-m-d', $from) . $close))
                        $i--;
                    if (strtotime(date('Y-m-d H:m', $to)) > strtotime(date('Y-m-d', $to) . $open))
                        $i++;
                    if (strtotime(date('Y-m-d H:m', $to)) <= strtotime(date('Y-m-d', $to) . $close))
                        $i--;
                }
                if (!$i) $available=true;
            }
        }
        return $available;
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
        

        //get things available for reservation and display them
        $things = Entity::where('type', '!=', 'amenity')->where('user_id', '=', $user->id)->get();
        foreach($things as $thing) {
            $this->info("{$thing->name} [{$thing->id}]");
        }
        do {
            $present = false;
            // ask user which thing he wants to book
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

        // get reservation's subject
        do {
            $subject = $this->ask("Subject : ");
            if(empty($subject))
                $this->comment("Your subject is invalid");
        } while(empty($subject));

        // get reservation's comment
        do {
            $comment = $this->ask("Comment : ");
            if(empty($comment))
                $this->comment("Your comment is invalid.");
        } while(empty($comment));

        //get reservation's timing
        do {

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
                $thing_body = json_decode($thing->body);
            } while(!$valid);
            if(!$this->isAvailable($thing_body->opening_hours, array('from' => $from, 'to' => $to)))
                $this->comment('The thing is not available at that time');
        } while(!$this->isAvailable($thing_body->opening_hours, array('from' => $from, 'to' => $to)));
        
        //TODO(qkaiser) : verify if room is available

        // get reservation's announcement
        $announce = $this->ask("Announce (names separated by a comma) : ");
        
        // create reservation object and save it to database
        $reservation = new Reservation;
        $reservation->user_id = $user->id;
        $reservation->entity_id = $thing_id;
        $reservation->subject = $subject;
        $reservation->comment = $comment;
        $reservation->from = date('c', $from);
        $reservation->to = date('c', $to);
        if(($announce = explode(",", $announce)) != null)
            $reservation->announce = json_encode($announce);
        $reservation->save();
        $this->info("Reservation successfully saved");
        return;
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