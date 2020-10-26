<?php
namespace Bumip\Core;

class CRUF
{
    private $dataManager;
    private $actionMap;
    public function __construct(object $dataManager = null, array $actionMap = ['get' => 'get', 'save' => 'save', 'getOne' => 'getOne'])
    {
        $this->actionMap = $actionMap;
        if ($dataManager) {
            $this->dataManager = $dataManager;
        }
    }
    private function crud(string $action, $query = null, array $data = null)
    {
        /**
         * /entities/ = get
         * /entities/user = get user entity
         * /entities/ ?data post
         */
        if ($data) {
            return $this->$action($query, $data);
        } else {
            return $this->$action($query);
        }
    }
    public function index(\Bumip\Core\Request $request = null)
    {
        if ($request) {
            $this->request = $request;
        }
        list($subject, $id) = [$this->request->index(1), $this->request->index(2)];
        $query = $id ? $id : null;
        $data = $this->request->data('data') ? $this->request->data('data') : null;
        //save = upsert (insert or update)
        $action = $data ? 'save' : 'get';
        if ($action == 'get' && $id) {
            $action = 'getOne';
        }
        return $this->crud($action, $query, $data);
    }
    private function get($query)
    {
        return $this->dataManager->{$this->actionMap['get']}($query);
    }
    private function getOne($query)
    {
        return $this->dataManager->{$this->actionMap['getOne']}($query);
    }
    private function save($query)
    {
        return $this->dataManager->{$this->actionMap['save']}($query);
    }
}
