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
        $auth = new Auth($this->user, [
            'id' => 'username',
            'pw' => 'password'
        ]);

        if($auth->login($_POST['username'], $_POST['password'])){
            $f3->set('SESSION.userId', $this->user->id);
        }

        $f3->reroute('@todo_list', true);
    }

    public function logout(Base $f3)
    {
        $f3->clear('SESSION.userId');

        $f3->reroute('@todo_list', true);
    }
}