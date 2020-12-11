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

            $order = $_GET['sort'] ?: '';

            $filter['0'] = 'user_id=:user_id';

            $filter[':user_id'] = $f3->get('SESSION.userId');

            if ($_GET['hide_completed'] === 'on') {
                $filter['0'] .= ' AND completed_at is null';
            }

            if ($_GET['hide_no_deadline'] === 'on') {
                $filter['0'] .= ' AND deadline is not null';
            }

            if ($_GET['todo_filter']) {
                $filter['0'] .= ' AND todo LIKE :todo_filter';
                
                $filter[':todo_filter'] = "%" . $_GET['todo_filter'] . "%";
            }

            $todos = $this->todo->paginate($page - 1, $take, $filter, [
                'order' => $this->determineOrder($order)
            ]);

            $f3->set('todos', $todos);

            $f3->set('extraScripts', [
                $f3->BASE . '/js/todo-list/index.js'
            ]);

            $f3->set('extraStyles', [
                $f3->BASE . '/css/todo-list/index.css'
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
            'todo' => ['required', 'max:255'],
            'deadline' => ['nullable', 'format:^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$'],
        ]);

        if ($validator->validate()) {
            $this->todo->todo = $_POST['todo'];

            $this->todo->deadline = $_POST['deadline'] ?: null;

            $this->todo->user_id = $f3->get('SESSION.userId');

            $this->todo->save();
        } else {
            $f3->FLASH->setKey('errors', $validator->errors());
        }

        $additionalParams = '';

        foreach ($_POST['additional_param_keys'] as $index => $key) {
            $additionalParams .= $index === 0 ? '?' : '&';

            $additionalParams .= "$key=" . $_POST[$key];
        }

        $f3->reroute("@todo_list$additionalParams", true);
    }

    public function toggleCompleted(Base $f3)
    {
        if (!$f3->exists('SESSION.userId')) {
            $f3->error(401, 'Unauthorized');
        }

        parse_str(file_get_contents("php://input"), $request);

        $validator = new Validator($request, [
            'todoId' => ['required', 'exists:todos,id'],
            'completed' => ['required'],
        ]);

        if ($validator->validate()) {
            $f3->DB->exec(
                'UPDATE todos SET completed_at = :completedAt WHERE id=:todoId',
                [
                    ':completedAt' => $request['completed'] === 'true' ? now() : null,
                    ':todoId' => $request['todoId'],
                ]
            );
        }

        echo json_encode($validator->errors());
    }

    /**
     * Update deadline
     * 
     * @param Base $f3 
     * @param array $params 
     * @return void 
     */
    public function updateDeadline(Base $f3, array $params): void
    {
        if (!$f3->exists('SESSION.userId')) {
            $f3->error(401, 'Unauthorized');
        }

        parse_str(file_get_contents("php://input"), $request);

        $validator = new Validator(array_merge($request, $params), [
            'deadline' => ['required', 'format:^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$'],
            'id' => ['required', 'exists:todos'],
        ]);

        if ($validator->validate()) {

            $this->todo->load(['id=?', $params['id']]);

            $this->todo->deadline = $request['deadline'];

            $this->todo->update();
        }

        echo json_encode($validator->errors());
    }

    /**
     * Update todo
     * 
     * @param Base $f3 
     * @param array $params 
     * @return void 
     */
    public function updateTodo(Base $f3, array $params): void
    {
        if (!$f3->exists('SESSION.userId')) {
            $f3->error(401, 'Unauthorized');
        }

        parse_str(file_get_contents("php://input"), $request);

        $validator = new Validator(array_merge($request, $params), [
            'todo' => ['required', 'max:255'],
            'id' => ['required', 'exists:todos'],
        ]);

        if ($validator->validate()) {
            $this->todo->load(['id=?', $params['id']]);

            $this->todo->todo = $request['todo'];

            $this->todo->update();
        }

        echo json_encode($validator->errors());
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
