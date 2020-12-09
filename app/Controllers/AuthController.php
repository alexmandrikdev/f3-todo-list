<?php

class AuthController
{
    private $user;

    public function __construct(Base $f3)
    {
        $this->user = new DB\SQL\Mapper($f3->DB, 'users');
    }

    public function loginView(Base $f3)
    {
        $f3->set('view', 'login.htm');

        echo Template::instance()->render('layout.htm');
    }

    public function login(Base $f3)
    {
        $this->user->load([
            'username=?', $_POST['username']
        ]);

        if ($this->user->dry() || !password_verify($_POST['password'], $this->user->password)) {
            $f3->set('errors', [
                'login' => 'Invalid username or password'
            ]);

            $f3->set('view', 'login.htm');

            echo Template::instance()->render('layout.htm');
        } else {
            $f3->set('SESSION.userId', $this->user->id);

            $f3->reroute('@todo_list', true);
        }
    }

    public function registerView(Base $f3)
    {
        $f3->set('view', 'register.htm');

        echo Template::instance()->render('layout.htm');
    }

    public function register(Base $f3)
    {
        $validator = new Validator($_POST, [
            'username' => ['required', 'min:4', 'max:45', 'unique:users,username'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        if (!$validator->validate()) {
            $f3->set('errors', $validator->errors());

            $f3->set('view', 'register.htm');

            $f3->set('old', $_POST);

            echo Template::instance()->render('layout.htm');
        } else {
            $this->user->username = $_POST['username'];

            $this->user->password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            $this->user->save();

            $f3->set('SESSION.userId', $this->user->id);

            $f3->reroute('@todo_list', true);
        }
    }

    public function logout(Base $f3)
    {
        $f3->clear('SESSION.userId');

        $f3->reroute('@login', true);
    }
}
