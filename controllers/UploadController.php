<?php

namespace app\controllers;

use Yii;
use app\models\Projects;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class UploadController extends Controller
{
    /**
     * Lists all Projects models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}