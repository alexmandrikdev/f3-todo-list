<?php

class TodoListController
{
    private $todo;

    public function __construct(Base $f3)
    {
        $this->todo = new Todo();
    }

    function beforeRoute(Base $f3)
    {
        if (!$f3->exists('SESSION.userId')) {
            $f3->reroute('@unauthorized', true);
        }
    }

    public function index(Base $f3): void
    {
        $todos = $this->todo->getForIndex();

        $f3->set('todos', $todos);

        $f3->set('extraScripts', [
            $f3->BASE . '/js/todo-list/index.js'
        ]);

        $f3->set('extraStyles', [
            $f3->BASE . '/css/todo-list/index.css'
        ]);

        $f3->set('view', 'todo-list/index.htm');

        echo Template::instance()->render('layout.htm');
    }

    public function store(Base $f3)
    {
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
        parse_str(file_get_contents("php://input"), $request);

        $validator = new Validator(array_merge($request, $params), [
            'todo' => ['required', 'max:255'],
            'id' => ['required', 'exists:todos'],
        ]);

        if ($validator->validate()) {
            $this->todo->load(['id=?', $params['id']]);

            $this->todo->todo = $request['todo'];

            $this->todo->update();
        } else {
            $f3->error(422, json_encode($validator->errors()));
        }
    }
}
