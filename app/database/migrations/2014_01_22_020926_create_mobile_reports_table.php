<?php

use Illuminate\Database\Migrations\Migration;

class CreateMobileReportsTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('mobile_reports', function($table) {
      $table->increments('id');
      $table->integer('user_id');
      $table->string('category');
      $table->string('message');
      $table->string('ip');
      $table->string('client');
      $table->string('report_type');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('mobile_reports');
  }

}