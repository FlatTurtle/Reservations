<?php


class CustomerTableSeeder extends Seeder {

	public function run()
    {
    	//check if table exists before deleting it.
        //DB::table('customer')->delete();
 
        DB::table('customer')->insert(array(
            'username' => 'test',
            'password' => Hash::make('test')
        ));
    }
}


?>