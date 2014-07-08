<?php
/*
 * Define factory data for tests suite
 */

Factory::define('User', function($f) {
  return [
    'name' => $f->next('name'),
    'username' => $f->next('username'),
    'email' => $f->next('email'),
    'password' => $f->next('password'),
    'usertype' => 2,
    'block' => 0,
    'registerDate' => '2014-07-07 00:00:00',
    'lastvisitDate' => '2014-07-07 00:00:00',
    'activation' => 0,
    'params' => '{}',
    'lastResetTime' => '0000-00-00 00:00:00',
    'resetCount' => 0
  ];
});

Factory::define('Report', function($f) {
  return [
    'category' => 'Testing',
    'message' => 'Testing report',
    'bug_type' => 'mobile-app'
  ];
});

/*
 * Factory Senquences
 */
Factory::sequence('name', function($n) {
  return "John Doe{$n}";
});

Factory::sequence('username', function($n) {
  return "johndoe{$n}";
});

Factory::sequence('password', function($n) {
  return "Password{$n}";
});

Factory::sequence('email', function($n) {
  return "test-{$n}@dirtyrottenrides.com";
});