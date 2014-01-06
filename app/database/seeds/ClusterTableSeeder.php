<?php


/**
 * Seeds the entity table by inserting data into it.
 * We insert two test users and an administrator user.
 * This is called by the artisan cli on 'artisan migrate --seed'
 */
use Hautelook\Phpass\PasswordHash;

class ClusterTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Schema::hasTable('cluster'))
            DB::table('cluster')->delete();

        $passwordHasher = new PasswordHash(8,false);
        if(Schema::hasTable('cluster'))
            DB::table('cluster')->delete();

        $test_user = DB::table('user')->where('username', '=', 'test')->first();

        DB::table('cluster')->insert(array(
            'clustername' => 'test',
            'password' => $passwordHasher->HashPassword('test'),
            'user_id' => $test_user->id
        ));

        $admin_user = DB::table('user')->where('username', '=', 'admin')->first();

        DB::table('cluster')->insert(array(
            'clustername' => 'admin',
            'password' => $passwordHasher->HashPassword('admin'),
            'user_id' => $admin_user->id
        ));
    }
}
