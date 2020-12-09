<?php

class TodoListController
{
    public function index(Base $f3): void
    {
        $todo = new DB\SQL\Mapper($f3->DB, 'todos');

        $page = $_GET['page'] ?: 1;

        $take = 10;

        $todos = $todo->paginate($page - 1, $take, [
            'user_id=?', 1
        ]);

        $f3->set('todos', $todos);

        $f3->set('extraScripts', [
            $f3->BASE . '/js/todo-list/index.js'
        ]);

        $f3->set('include', 'todo-list/index.htm');

        echo Template::instance()->render('layout.htm');
    }

    public function update(Base $f3)
    {
        parse_str(file_get_contents("php://input"), $request);

        $f3->DB->exec(
            'UPDATE todos SET completed_at = :completedAt WHERE id=:todoId',
            [
                ':completedAt' => $request['completed'] === 'true' ? now() : null,
                ':todoId' => $request['todoId'],
            ]
        );
    }
}
