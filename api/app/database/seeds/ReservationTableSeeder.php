<?php


class ReservationTableSeeder extends Seeder {

	public function run()
    {
    	if(Schema::hasTable('reservation'))
	 		DB::table('reservation')->delete();
        
    }
}


?>