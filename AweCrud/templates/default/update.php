<?php
echo "<?php\n";
$label = $this->pluralize($this->class2name($this->modelClass));
echo "\$this->breadcrumbs = array(
    Yii::t('app', '$label') => array('index'),
    Yii::t('app', \$model->{$this->getIdentificationColumn()}) => array('view','{$this->tableSchema->primaryKey}'=>\$model->{$this->tableSchema->primaryKey}),
    Yii::t('app', 'Update'),
);";
?>

if(!isset($this->menu) || $this->menu === array())
$this->menu=array(
	array('label'=>Yii::t('app', 'Delete') , 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model-><?php echo $this->tableSchema->primaryKey; ?>),'confirm'=>'Are you sure you want to delete this item?')),
	//array('label'=>Yii::t('app', 'Create') , 'url'=>array('create')),
	//array('label'=>Yii::t('app', 'Manage') , 'url'=>array('admin')),
);
?>

<?php 
$pk = "\$model->" . $this->getIdentificationColumn();
printf('<h1> %s %s </h1>',
'<?php echo Yii::t(\'app\', \'Update\');?>',
'<?php echo ' . $pk . '; ?>'); ?>

<?php echo "<?php\n"; ?>
$this->renderPartial('_form', array(
			'model'=>$model));
?>