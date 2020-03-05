<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\JiraAlert */

$this->title = 'Create Jira Alert';
$this->params['breadcrumbs'][] = ['label' => 'Jira Alerts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jira-alert-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
