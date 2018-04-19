<?php

namespace Jiko\Discord\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
  public $guarded = [];

  function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    $this->setConnection('alpha');
  }

  public function roles()
  {
    return $this->hasMany('Jiko\Discord\Models\Role');
  }
}