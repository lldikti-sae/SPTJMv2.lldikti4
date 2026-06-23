<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = ['email', 'password', 'role', 'active', 'cp', 'admin_permissions'];

  protected $hidden = ['password', 'remember_token'];

  protected $casts = [
      'admin_permissions' => 'array',
  ];

  public function isAdmin()
  {
    return $this->role === 'admin';
  }

  public function isPIC()
  {
    return $this->role === 'pic';
  }

  public function isPTS()
  {
    return $this->role === 'pts';
  }

  public function hasAdminPermission($permission)
  {
      if ($this->isAdmin()) {
          return true;
      }
      
      if ($this->isPIC()) {
          $permissions = $this->admin_permissions ?? [];
          return in_array($permission, $permissions);
      }
      
      return false;
  }
}
