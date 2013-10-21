<?php

use Hautelook\Phpass\PasswordHash;
class CustomerTableSeeder extends Seeder {

	public function run()
    {
        $passwordHasher = new PasswordHash(8,false);
		if(Schema::hasTable('customer'))
	 		DB::table('customer')->delete();

        DB::table('customer')->insert(array(
            'username' => 'test',
            'password' => $passwordHasher->HashPassword('test')
        ));

        DB::table('customer')->insert(array(
            'username' => 'test2',
            'password' => $passwordHasher->HashPassword('test')
        ));
    }
}


?>