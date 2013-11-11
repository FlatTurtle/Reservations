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
        // checking the existence of the user table before adding
        if(!Schema::hasTable('user')){
            Schema::create('user', function(Blueprint $table)
            {
                $table->increments('id');
                $table->timestamps();
                $table->string('username');
                $table->string('password');
                $table->integer('rights');
            });
        }else{
            if(!Schema::hasColumn('user', 'username')){
                Schema::table('user', function($table)
                {
                    $table->string('username');
                });
            }
            if(!Schema::hasColumn('user', 'password')){
                Schema::table('user', function($table)
                {
                    $table->string('password');
                });
            }
            if(!Schema::hasColumn('user', 'rights')){
                Schema::table('user', function($table)
                {
                    $table->integer('rights');
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

		DB::table('user')->delete();
		Schema::drop('user');

	}

}
