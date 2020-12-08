<?php

class TodoListController
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

        $f3->set('extraScripts', [
            $f3->BASE . '/js/todo-list/index.js'
        ]);

        $f3->set('include', 'todo-list/index.htm');

        echo Template::instance()->render('layout.htm');
    }

    public function update(Base $f3)
    {
        $f3->DB->exec(
            'UPDATE todos SET completed_at = :completedAt WHERE id=:todoId',
            [
                ':completedAt' => $f3->get('POST.completed') ? now() : null,
                ':todoId' => $f3->get('POST.todoId'),
            ]
        );
    }
}
