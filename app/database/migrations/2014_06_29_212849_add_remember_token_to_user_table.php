<?php

use Illuminate\Database\Migrations\Migration;

class AddRememberTokenToUserTable extends Migration {

        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
                Schema::table('user', function($table){
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
                Schema::table('user', function($table){
                        $table->dropColumn('remember_token');
                });
        }

}
