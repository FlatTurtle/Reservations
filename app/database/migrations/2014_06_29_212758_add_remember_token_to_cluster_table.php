<?php

use Illuminate\Database\Migrations\Migration;

class AddRememberTokenToClusterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cluster', function($table){
			 $table->string('remember_token',100);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cluster', function($table){
            		$table->dropColumn('remember_token');
        	});
	}
}
