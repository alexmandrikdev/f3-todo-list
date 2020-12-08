<?php

class TodoListController extends Controller
{
    public function index(Base $f3): void
    {
        $f3->set('todos', $f3->DB->exec(
                'SELECT * FROM todos ' .
                'WHERE user_id=:user_id',
            [
                ':user_id' => 1
            ]
        ));

        $f3->set('include', 'todo-list/index.htm');
    }
}