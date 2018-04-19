<?php

namespace Jiko\Discord\Models;

use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
  public $guarded = [];

  function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    $this->setConnection('alpha');
  }
}