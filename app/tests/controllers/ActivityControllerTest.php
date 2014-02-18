<?php

class ActivityControllerTest extends TestCase {

  public function __construct()
  {
    $this->user = Mockery::mock('Eloquent', 'User');
    //$this->activity = Mockery::mock('Eloquent', 'Activity');

    $this->user
      ->shouldReceive('Find_id_by_hash')
      ->once()
      ->andReturn([
        'id' => 1,
        'name' => 'John Doe',
        'username' => 'johndoe',
        'user_hash' => '123456789'
      ]);
  }

  public function tearDown()
  {
    Mockery::close();
  }

  // Save new activity record
  public function testStore()
  {
    $this->app->instance('User', $this->user);
    //$this->app->instance('Activity', $this->activity);

    $this->post('user/activity?user_hash=b567b3a9b7983034e99d73d3064b7fe8d6bc7ecec73551173');

    $this->assertResponseOk();
  }
}