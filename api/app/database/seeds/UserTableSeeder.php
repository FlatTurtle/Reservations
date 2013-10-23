<?php


class UserTableSeeder extends Seeder {

	public function run()
    {
		if(Schema::hasTable('user'))
	 		DB::table('user')->delete();

        DB::table('user')->insert(array(
            'username' => 'test',
            'password' => Hash::make('test'),
            'rights' => 0
        ));

        DB::table('user')->insert(array(
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'rights' => 100
        ));


        DB::table('user')->insert(array(
            'username' => 'test2',
            'password' => Hash::make('test2'),
            'rights' => 0
        ));
    }
}


?>