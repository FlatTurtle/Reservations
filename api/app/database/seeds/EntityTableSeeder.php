<?php


class EntityTableSeeder extends Seeder {

	public function run()
    {
    	if(Schema::hasTable('entity'))
 			DB::table('entity')->delete();
        
    }
}


?>