<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * This class is called by artisan when using the artisan migrate cli.
 * It create the user table on up and drop it on down.
 */
class CreateUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->string('username');
			$table->string('password');
			$table->integer('rights');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

		DB::table('user')->delete();
		Schema::drop('user');

	}

}
