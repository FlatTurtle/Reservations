<?php

use Illuminate\Database\Migrations\Migration;

class AddCodeColumnOnReservation extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('reservation', function($table)
        {
            $table->string('code');
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
            $table->dropColumn('code');
        });

	}

}
