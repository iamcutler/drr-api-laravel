<?php

class ActivityControllerTest extends TestCase {

  public function __construct()
  {
    $this->user = Mockery::mock('Eloquent', 'User');
    //$this->activity = Mockery::mock('Eloquent', 'Activity');

    /*$this->user
      ->shouldReceive('Find_id_by_hash')
      ->once()
      ->andReturn([
        'id' => 1,
        'name' => 'John Doe',
        'username' => 'johndoe',
        'user_hash' => '123456789'
      ]);*/
  }

  public function tearDown()
  {
    Mockery::close();
  }

  /** @test */
  public function it() {

  }
}