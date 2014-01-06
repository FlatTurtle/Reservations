<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('UserTableSeeder');
		$this->call('ClusterTableSeeder');
		$this->call('EntityTableSeeder');
		$this->call('ReservationTableSeeder');
        $this->call('CompanyTableSeeder');
	}

}
