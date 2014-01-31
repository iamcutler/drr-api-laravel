<?php

class DatabaseSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Eloquent::unguard();

    $this->call('UserTableSeeder');
    $this->command->info('User Table Seeded!');
  }
}

class UserTableSeeder extends Seeder {
  public function run()
  {
    DB::table('users')->delete();

    User::create([
      'name' => 'John Doe',
      'username' => 'john-doe',
      'email' => 'john@doe.com',
      'password' => 'TEST',
      'usertype' => 2,
      'block' => 0,
      'registerDate' => date("Y-m-d H:i:s"),
      'lastvisitDate' => date("Y-m-d H:i:s"),
      'activation' => 0,
      'params' => ''
    ]);
  }
}