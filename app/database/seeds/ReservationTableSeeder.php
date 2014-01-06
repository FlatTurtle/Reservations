<?php


/**
 * Seeds the reservation table by inserting data into it.
 * This is called by the artisan cli on 'artisan migrate --seed'
 */
class ReservationTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
    {
    	if(Schema::hasTable('reservation'))
	 		DB::table('reservation')->delete();

    }
}
