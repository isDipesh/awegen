<?php
$label = $this->pluralize($this->class2name($this->modelClass));

echo "<?php\n";
echo "\$this->breadcrumbs = array(
    Yii::t('app', '$label')
);"
?>

if(!isset($this->menu) || $this->menu === array())
$this->menu=array(
	array('label'=>Yii::t('app', 'Create'), 'url'=>array('create')),
	array('label'=>Yii::t('app', 'Manage'), 'url'=>array('admin')),
);
?>

<h1><?php echo $label; ?></h1>

<?php echo "<?php"; ?> $this->widget('bootstrap.widgets.BootListView', array(
'dataProvider'=>$dataProvider,
'itemView'=>'_view',
)); ?>
