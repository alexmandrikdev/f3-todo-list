<?php

class TodoListController
{
    private $todo;

    public function __construct(Base $f3)
    {
        $this->todo = new DB\SQL\Mapper($f3->DB, 'todos');
    }

    public function index(Base $f3): void
    {
        if ($f3->exists('SESSION.userId')) {
            $page = $_GET['page'] ?: 1;

            $take = 10;

            $order = $_GET['order'] ?: '';

            $todos = $this->todo->paginate($page - 1, $take, [
                'user_id=?', $f3->get('SESSION.userId')
            ], [
                'order' => $this->determineOrder($order)
            ]);

            $f3->set('todos', $todos);

            $f3->set('extraScripts', [
                $f3->BASE . '/js/todo-list/index.js'
            ]);

            $f3->set('view', 'todo-list/index.htm');
        } else {
            $f3->set('view', 'unauthorized.htm');
        }

        echo Template::instance()->render('layout.htm');
    }

    public function store(Base $f3)
    {
        if (!$f3->exists('SESSION.userId')) {
            $f3->error(401, 'Unauthorized');
        }

        $validator = new Validator($_POST, [
            'todo' => ['required']
        ]);

        if ($validator->validate()) {
            $this->todo->todo = $_POST['todo'];

            $this->todo->deadline = $_POST['deadline'] ?: null;

            $this->todo->user_id = $f3->get('SESSION.userId');

            $this->todo->save();
        }

        $f3->reroute('@todo_list', true);
    }

    public function toggleCompleted(Base $f3)
    {
        if (!$f3->exists('SESSION.userId')) {
            $f3->error(401, 'Unauthorized');
        }

        parse_str(file_get_contents("php://input"), $request);

        $f3->DB->exec(
            'UPDATE todos SET completed_at = :completedAt WHERE id=:todoId',
            [
                ':completedAt' => $request['completed'] === 'true' ? now() : null,
                ':todoId' => $request['todoId'],
            ]
        );
    }

    /**
     * Determine the order based on the $order param
     * 
     * @param string $order 
     * @return string 
     */
    private function determineOrder(string $order): string
    {
        switch ($order) {
            case 'name':
                return 'todo';
                break;

            case 'name_desc':
                return 'todo DESC';
                break;

            case 'deadline':
                return 'deadline';
                break;

            case 'deadline_desc':
                return 'deadline DESC';
                break;

            case 'add_date':
                return 'id';
                break;
            
            default:
                return 'id DESC';
                break;
        }
    }
}
