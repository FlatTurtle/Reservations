<?php


/**
 * Seeds the entity table by inserting data into it.
 * We insert two test users and an administrator user.
 * This is called by the artisan cli on 'artisan migrate --seed'
 */
use Hautelook\Phpass\PasswordHash;

class UserTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
	public function run()
    {
        $passwordHasher = new PasswordHash(8,false);
		if(Schema::hasTable('user'))
	 		DB::table('user')->delete();

        DB::table('user')->insert(array(
            'username' => 'test',
            'password' => $passwordHasher->HashPassword('test'),
            'rights' => 0
        ));

        DB::table('user')->insert(array(
            'username' => 'admin',
            'password' => $passwordHasher->HashPassword('admin'),
            'rights' => 100
        ));


        DB::table('user')->insert(array(
            'username' => 'test2',
            'password' => $passwordHasher->HashPassword('test2'),
            'rights' => 0
        ));
    }
}


?>