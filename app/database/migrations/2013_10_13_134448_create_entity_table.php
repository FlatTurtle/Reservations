<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * This class is called by artisan when using the artisan migrate cli.
 * It create the entity table on up and drop it on down.
 */
class CreateEntityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('entity', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->string('name');
			$table->string('type');
			$table->text('body');
			// ? $table->binary('body');
			$table->integer('user_id');
			//TODO : find a way to add foreign keys correctly
			//$table->foreign('customer_id', 'customer_id')->references('id')->on('customer');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('entity')->delete();
		Schema::drop('entity');
	}

}
