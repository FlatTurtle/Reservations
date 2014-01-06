<?php

/**
 * Seeds the entity table by inserting data into it.
 * This is called by the artisan cli on 'artisan migrate --seed'
 */
class EntityTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
    {
    	if(Schema::hasTable('entity'))
 			DB::table('entity')->delete();

    }
}
