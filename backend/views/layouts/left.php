<?php

use common\models\User;

$adminMenu = [
    'options' => ['class' => 'sidebar-menu'],
    'items' => [
        ['label' => 'Menu', 'options' => ['class' => 'header']],
        ['label' => 'Dashboard', 'icon' => 'fa fa-dashboard', 'url' => ['/']],
        [
            'label' => 'Cameras',
            'icon' => 'fa fa-video-camera',
            'url' => '#',
            'items' => [
                ['label' => 'Camera List', 'url' => ['/camera/'], 'options' => ['class' => 'prioriry-load']],
                ['label' => 'Add camera', 'url' => ['/camera/create'], 'options' => ['class' => 'prioriry-load']],
            ],
        ],
        [
            'label' => 'Properties',
            'icon' => 'fa fa-home',
            'url' => '#',
            'items' => [
                ['label' => 'Property List', 'url' => ['/property/'], 'options' => ['class' => 'prioriry-load']],
                ['label' => 'Add property', 'url' => ['/property/create'], 'options' => ['class' => 'prioriry-load']],
            ],
        ],
        [
            'label' => 'RFID Readers',
            'icon' => 'fa fa-home',
            'url' => '#',
            'items' => [
                ['label' => 'RFID Reader List', 'url' => ['/reader/index'], 'options' => ['class' => 'prioriry-load']],
                ['label' => 'Add RFID Reader', 'url' => ['/reader/create'], 'options' => ['class' => 'prioriry-load']],
            ],
        ],
        [
            'label' => 'RFID Tags',
            'icon' => 'fa fa-home',
            'url' => '#',
            'items' => [
                ['label' => 'RFID Tag List', 'url' => ['/tag/index'], 'options' => ['class' => 'prioriry-load']],
                ['label' => 'Add RFID Tag', 'url' => ['/tag/create'], 'options' => ['class' => 'prioriry-load']],
            ],
        ],
        ['label' => 'Alerts', 'icon' => 'fa fa-bell-o', 'url' => ['/alert/'], 'options' => ['class' => 'prioriry-load']],
        ['label' => 'Alarms', 'icon' => 'fa fa-bell', 'url' => ['/alarm/'], 'options' => ['class' => 'prioriry-load']],
        [
            'label' => 'Users',
            'icon' => 'fa fa-users',
            'url' => '#',
            'items' => [
                ['label' => 'User List', 'url' => ['/user/'], 'options' => ['class' => 'prioriry-load']],
                ['label' => 'Create User', 'url' => ['/user/create'], 'options' => ['class' => 'prioriry-load']],
            ],
        ],
        ['label' => 'Logs', 'icon' => 'fa fa-history', 'url' => ['/log/'], 'options' => ['class' => 'prioriry-load']],
        ['label' => 'Tag Logs', 'icon' => 'fa fa-history', 'url' => ['/tag-log/'], 'options' => ['class' => 'prioriry-load']],
        ['label' => 'Settings', 'icon' => 'fa fa-cogs', 'url' => ['/settings/'], 'options' => ['class' => 'prioriry-load']],
        ['label' => 'Downloads', 'options' => ['class' => 'header'], 'options' => ['class' => 'prioriry-load']],
        ['label' => 'Android App', 'icon' => 'fa fa-android', 'url' => ['/download/Patroleum.apk'], 'options' => ['class' => 'prioriry-load']],
    ],
];
$agentMenu = [
    'options' => ['class' => 'sidebar-menu'],
    'items' => [
        ['label' => 'Menu', 'options' => ['class' => 'header']],
        ['label' => 'Dashboard', 'icon' => 'fa fa-dashboard', 'url' => ['/'], 'options' => ['class' => 'prioriry-load']],
        ['label' => 'Camera panel', 'icon' => 'fa fa-video-camera', 'url' => '/watch', 'options' => ['class' => 'prioriry-load']],
        ['label' => 'Alerts', 'icon' => 'fa fa-bell-o', 'url' => ['/alert/recieved'], 'options' => ['class' => 'prioriry-load']],
        ['label' => 'Alarms', 'icon' => 'fa fa-bell', 'url' => ['/alarm/'], 'options' => ['class' => 'prioriry-load']],
        ['label' => 'Logs', 'icon' => 'fa fa-history', 'url' => ['/log/'], 'options' => ['class' => 'prioriry-load']],
    ],
];

//  Show agents not closed alerts
if (Yii::$app->user->getIdentity()->role == User::ROLE_AGENT) {
    $agentAlerts = \common\models\Alert::find()
            ->andWhere(['user_id' => Yii::$app->user->id])
            ->andWhere(['or', ['status' => \common\models\Alert::STATUS_ACCEPTED], ['status' => \common\models\Alert::STATUS_ASSIGNED]])
            ->all();
    if (count($agentAlerts) > 0)
        $agentMenu['items'][] = ['label' => 'Assigned alert', 'options' => ['class' => 'header']];
    foreach ($agentAlerts as $curAlert) {
        $agentMenu['items'][] = ['label' => 'Alert #' . $curAlert->id . ' - ' . Yii::$app->formatter->asTime($curAlert->created_at), 'icon' => 'fa fa-bell-o', 'url' => ['/site/watch', 'alert_id' => $curAlert->id]];
    }
}

switch (Yii::$app->user->getIdentity()->role) {
    case User::ROLE_ADMIN:
        $menuOptions = $adminMenu;
        break;
    case User::ROLE_AGENT:
        $menuOptions = $agentMenu;
        break;
    default:
        $menuOptions = $admintMenu;
        break;
}
?>

<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="/img/User-100.png" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?= (Yii::$app->user->isGuest ? "" : ucfirst(Yii::$app->user->getIdentity()->username)) ?></p>

                <p><span id='ws-connection-status'><i class="fa fa-circle text-success"></i> Online</span></p>
            </div>
        </div>

        <?php
        /*
          <!-- search form -->
          <form action="#" method="get" class="sidebar-form">
          <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search..."/>
          <span class="input-group-btn">
          <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
          </button>
          </span>
          </div>
          </form>
          <!-- /.search form -->
         */
        ?>


        <?=
        dmstr\widgets\Menu::widget(
                $menuOptions
        )
        ?>

    </section>

</aside>
