<?php

class ReportControllerTest extends TestCase {

  public function setUp()
  {
    $user = Mockery::mock('Eloquent', 'User');
  }
  public function tearDown()
  {
    Mockery::close();
  }

  public function testBug()
  {

  }
}