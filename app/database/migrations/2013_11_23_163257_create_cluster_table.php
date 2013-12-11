<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClusterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		
		if(!Schema::hasTable('cluster')){
            Schema::create('cluster', function(Blueprint $table)
            {
                $table->increments('id');
                $table->timestamps();
				$table->string('clustername');
				$table->string('password');
				$table->integer('user_id');
            });
        }else{
            if(!Schema::hasColumn('cluster', 'clustername')){
                Schema::table('cluster', function($table)
                {
                    $table->string('clustername');
                });
            }
            if(!Schema::hasColumn('cluster', 'password')){
                Schema::table('cluster', function($table)
                {
                    $table->string('password');
                });
            }
            if(!Schema::hasColumn('cluster', 'user_id')){
                Schema::table('cluster', function($table)
                {
                    $table->integer('user_id');
                });
            }
        }

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if(Schema::hasTable('cluster')){
			DB::table('cluster')->delete();
			Schema::drop('cluster');
		}
	}

}