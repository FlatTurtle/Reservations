<?php


class CustomerTableSeeder extends Seeder {

	public function run()
    {
		if(Schema::hasTable('customer'))
	 		DB::table('customer')->delete();
        DB::table('customer')->insert(array(
            'username' => 'test',
            'password' => Hash::make('test')
        ));
    }
}


?>