<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

$this->render('/camera/_vlc-js');

$actionId = Yii::$app->controller->action->id;
$controllerId = Yii::$app->controller->id;
$initJs = <<<SCRIPT
   //   Logging options
   var logUserActivity = function(){
       $.post("/log/write",{name: "User online",details: JSON.stringify({controller: "{$controllerId}", action: "{$actionId}"})});
   }
   logUserActivity();
   setInterval(logUserActivity, 20000);  
SCRIPT;
$this->registerJs($initJs, yii\web\View::POS_READY);

if (class_exists('backend\assets\AppAsset')) {
    backend\assets\AppAsset::register($this);
} else {
    app\assets\AppAsset::register($this);
}

dmstr\web\AdminLteAsset::register($this);

$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');

//  Add bower assets
$this->registerAssetBundle(kartik\growl\GrowlAsset::className());

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
        <?php $this->beginBody() ?>
        <div class="wrapper">

            <?=
            $this->render(
                    'header', ['directoryAsset' => $directoryAsset]
            )
            ?>

            <?=
            $this->render(
                    'left', ['directoryAsset' => $directoryAsset]
            )
            ?>

            <div class="content-wrapper">
                <?= $content ?>
            </div>

        </div>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
