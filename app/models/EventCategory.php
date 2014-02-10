<?php

class EventCategory extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_events_category";

  /**
   * Mass assignment
   */
  protected $fillable = [];
  protected $guarded = ['id'];
}