<?php

class Todo extends DB\SQL\Mapper
{
    private $f3;

    function __construct()
    {
        $this->f3 = Base::instance();

        parent::__construct($this->f3->DB, 'todos');
    }

    function getForIndex()
    {
        $page = $_GET['page'] ?: 1;

        $take = 10;

        $order = $_GET['sort'] ?: '';

        $filter['0'] = 'user_id=:user_id';

        $filter[':user_id'] = $this->f3->get('SESSION.userId');

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

        return $this->paginate($page - 1, $take, $filter, [
            'order' => $this->determineOrder($order)
        ]);
    }

    function create()
    {
        $this->todo = $_POST['todo'];

        $this->deadline = $_POST['deadline'] ?: null;

        $this->user_id = $this->f3->get('SESSION.userId');

        $this->save();
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
