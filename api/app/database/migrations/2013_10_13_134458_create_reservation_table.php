<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reservation', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->timestamp('from');
			$table->timestamp('to');
			$table->string('subject');
			$table->text('comment');
			$table->integer('customer_id');
			$table->integer('entity_id');
			//TODO : find a way to add foreign keys correctly
			//$table->foreign('customer_id', 'customer_id')->references('id')->on('customer');
			//$table->foreign('entity_id', 'entity_id')->references('id')->on('entity');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('reservation')->delete();
		Schema::drop('reservation');
	}

}
