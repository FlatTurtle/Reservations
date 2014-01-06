<?php

use Illuminate\Database\Migrations\Migration;

class AddActiveColumnOnReservation extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('reservation', function($table)
        {
            $table->boolean('activated')->default(true);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('reservation', function($table)
        {
            $table->dropColumn('activated');
        });

	}

}