<?php

namespace Jiko\Discord\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Playlist extends Model
{
  use SoftDeletes;

  public $guarded = [];

  function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    $this->setConnection('alpha');
  }
}