    public function filters() {
        return array(
            'accessControl - login, logout',
        );
    }

    public function accessRules() {
        return array(
            array('allow',
                'users' => array('admin'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }
