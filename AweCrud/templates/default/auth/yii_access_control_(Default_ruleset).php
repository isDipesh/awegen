public function filters() {
    return array(
            'accessControl - login, logout', 
            );
}

public function accessRules() {
    return array(
            array('allow',
                'actions'=>array('index','view'),
                'users'=>array('*'),
                ),
            array('allow', 
                'actions'=>array('minicreate', 'create','update'),
                'users'=>array('@'),
                ),
            array('allow', 
                'actions'=>array('manage','delete', 'toggle'),
                'users'=>array('admin'),
                ),
            array('deny', 
                'users'=>array('*'),
                ),
            );
}
