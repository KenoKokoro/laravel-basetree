<?php


namespace BaseTree\Tests\Fake\Integration;


use BaseTree\Models\BaseTreeModel;
use Illuminate\Database\Eloquent\Model;

class UserModel extends Model implements BaseTreeModel
{
    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'remember_token'];
}