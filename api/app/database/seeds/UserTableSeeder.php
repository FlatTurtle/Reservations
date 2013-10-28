<?php


/**
 * Seeds the entity table by inserting data into it.
 * We insert two test users and an administrator user.
 * This is called by the artisan cli on 'artisan migrate --seed'
 */
class UserTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
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