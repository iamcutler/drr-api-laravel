<?php

class ReportTest extends TestCase {
  /** @test */
  public function validation_fails_without_category_attribute()
  {
    $report = Factory::build('Report', [
      'category' => ''
    ]);

    $this->assertFalse($report->validate(Input::all()), 'Expected to fail without cvategory attribute');
  }

  /** @test */
  public function validation_fails_without_message_attribute()
  {
    $report = Factory::build('Report', [
      'message' => ''
    ]);

    $this->assertFalse($report->validate(Input::all()), 'Expected to fail without message attribute');
  }

  /** @test */
  public function validation_fails_without_app_type_attribute()
  {
    $report = Factory::build('Report', [
      'bug_type' => ''
    ]);

    $this->assertFalse($report->validate(Input::all()), 'Expected to fail without bug_type attribute');
  }

  /** @test */
  public function validates_all_required_attributes()
  {
    $report = Factory::build('Report');

    $this->assertTrue($report->validate(Input::all()), 'Expected validation to pass');
  }
} 