<?php
/**
 * Seeds the entity table by inserting data into it.
 * This is called by the artisan cli on 'artisan migrate --seed'
 */
class CompanyTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Schema::hasTable('company'))
            DB::table('company')->delete();

    }
}


