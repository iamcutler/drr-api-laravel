<?php

class UserTest extends TestCase {

  public function setUp()
  {
    parent::setUp();
  }

  /* Validation Tests */
  /** @test */
  public function validation_fails_without_email_attribute()
  {
    $user = Factory::build('User', [
      'email' => ''
    ]);

    $this->assertFalse($user->validate(Input::all()), 'Expected validation to fail with no provided email address.');
  }

  /** @test */
  public function validation_fails_without_username_attribute()
  {
    $user = Factory::build('User', [
      'username' => ''
    ]);

    $this->assertFalse($user->validate(Input::all()), 'Expected validation to fail with no provided username.');
  }

  /** @test */
  public function validation_passes_with_all_required_attributes()
  {
    $user = Factory::build('User');

    $this->assertTrue($user->validate(Input::all()), 'Expected validation to pass with all provided Attributes');
  }
}