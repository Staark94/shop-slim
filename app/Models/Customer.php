<?php

namespace Cart\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'localitate',
        'judet'
    ];

    public static $allowNotAuth = [
        "/auth/login",
        "/auth/register",
        "/auth/reset"
    ];

    protected $table = 'customers';

    public function userExists($user_name = "Guest") {
        $name = $this->where('name', $user_name)->first();

        if(!$name) {
            return true;
        }
    }

    public function mailExists($mail = "") {
        $mail = $this->where('email', $mail)->first();

        if(!$mail) {
            return true;
        }
    }

    public function getId()
    {
        $id = $this->select('id')->where('name', $this->user())->first();
        return $id;
    }

    public function checkPassword($pass = "") {
        $pass_db = $this->where('password', $pass)->first();

        if (!$pass) {
            return false;
        }

        if (password_verify($pass, $pass_db)) {
            return true;
        }
    }

    public function logged() {
        if (isset($_COOKIE['logged'])) {
            return true;
        }
    }

    public function user() {
        return isset($_COOKIE['username']) ? $_COOKIE['username'] : 'Guest';
    }

    public function admin() {
        $is = $this->select('guard')->where('name', $this->user())->first();
        if (!$is) {
            return;
        }

        if ($is->guard == 1) return true;
    }


    public function user_avatar()
    {
        return (isset($this->avatar)) ?: 'https://shop-piesetv.ro/resources/views/images/shop-thumbnail.jpg';
    }

    public function data($field = "") {
        $is = $this->select($field)->where('name', $this->user())->get();
        if(!$is) return;

        return $is[0]->$field;
    }
}