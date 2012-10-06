<?php
echo "<?php\n";
$label=$this->pluralize($this->class2name($this->modelClass));
echo "\$this->breadcrumbs = array(
    Yii::t('app', '$label') => array('index'),
    Yii::t('app', 'Create'),
);"
?>

if(!isset($this->menu) || $this->menu === array())
$this->menu=array(
	array('label'=>Yii::t('app', 'List'), 'url'=>array('index')),
	array('label'=>Yii::t('app', 'Manage'), 'url'=>array('admin')),
);
?>

<?php printf('<h1> %s %s </h1>', Yii::t('app', 'Create New'), $this->modelClass); ?>

<?php echo "<?php\n"; ?>
$this->renderPartial('_form', array(
			'model' => $model,
			'buttons' => 'create'));

?>