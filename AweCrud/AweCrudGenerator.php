<?php

class AweCrudGenerator extends CCodeGenerator {

    public $codeModel = 'ext.awegen.AweCrud.AweCrudCode';

    protected function getModels() {
        $models = array();
        $aliases = array();
        $aliases[] = 'application.models';
        foreach (Yii::app()->getModules() as $moduleName => $config) {
            if ($moduleName != 'gii')
                $aliases[] = $moduleName . ".models";
        }

        foreach ($aliases as $alias) {
            if (!is_dir(Yii::getPathOfAlias($alias)))
                continue;
            $files = scandir(Yii::getPathOfAlias($alias));
            Yii::import($alias . ".*");
            foreach ($files as $file) {
                if ($fileClassName = $this->checkFile($file, $alias)) {
                    $classname = sprintf('%s.%s', $alias, $fileClassName);
                    Yii::import($classname);
                    try {
                        $model = @new $fileClassName;
                        if (is_object($model) && $model->getMetaData())
                            $models[$classname] = $model->attributes;
                        else
                            $models[$classname] = array();
                    } catch (ErrorException $e) {
                        break;
                    } catch (CDbException $e) {
                        break;
                    } catch (Exception $e) {
                        break;
                    }
                }
            }
        }

        return $models;
    }

    private function checkFile($file, $alias = '') {
        if (substr($file, 0, 1) !== '.'
                && substr($file, 0, 2) !== '..'
                && substr($file, 0, 4) !== 'Base'
                && $file != 'GActiveRecord'
                && strtolower(substr($file, -4)) === '.php') {
            $fileClassName = substr($file, 0, strpos($file, '.'));
            if (class_exists($fileClassName)
                    && is_subclass_of($fileClassName, 'CActiveRecord')) {
                $fileClass = new ReflectionClass($fileClassName);
                if ($fileClass->isAbstract())
                    return null;
                else
                    return $models[] = $fileClassName;
            }
        }
    }

    protected function getAuthTypes() {
        return array_map('Awecms::getScriptName', glob(Yii::getPathOfAlias('ext.awegen.AweCrud.templates.default.auth') . '/*.php'));
    }
    
}