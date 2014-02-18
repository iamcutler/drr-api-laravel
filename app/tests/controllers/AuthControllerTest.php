<?php
class AuthControllerTest extends TestCase {
  public function tearDown()
  {
    Mockery::close();
  }

  public function testLogin()
  {
    //$mock = Mockery::mock('Eloquent', 'User');
    //$mock->
    //$mock->shouldReceive('where')->once()->passthru();

    $this->call('POST', 'user/login');
    $this->assertResponseOk();
  }
}