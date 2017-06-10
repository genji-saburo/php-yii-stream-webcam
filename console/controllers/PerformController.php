<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use \common\models\Alert;
use \common\models\Camera;
use \common\models\Property;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use common\models\LogStream;
use common\models\PropertyAccessLog;
use yii\helpers\Html;

class PerformController extends Controller {

    /**
     * Adds new active alert
     * 
     * @param type $camera_id
     */
    public function actionAddAlert($camera_id = 1) {
        $model = Camera::findOne($camera_id);
        if ($model) {
            if (Alert::add($model->serial_number, "127.0.0.1", Alert::EVENT_VIDEO_MOTION))
                $this->stdout("New alert has been successfully created" . PHP_EOL);
            else
                $this->stdout("Unexpected error while alert creation" . PHP_EOL);
        }
        else {
            $this->stdout("Failed. No camera fount with given id" . PHP_EOL);
        }
    }

    /**
     * Sends email with given parameters
     * 
     * @param type $emailTo
     * @param type $subject
     * @param type $message
     */
    public function actionSendMail($emailTo, $subject, $message) {
        $result = Yii::$app->mail->compose()
                ->setTo($emailTo)
                ->setFrom("noreply@platform.patroleum.net")
                ->setSubject($subject)
                ->setTextBody($message)
                ->send();
        if ($result)
            $this->stdout("Email has been succesfully sent" . PHP_EOL);
        else
            $this->stdout("Unexpected error while sending email" . PHP_EOL);
    }

    /**
     * Download image for test purpose - remove if not needed
     * 
     * @param type $id
     */
    public function actionRefreshCameraImg($id) {
        if ($id && ($camera = Camera::findOne($id))) {
            $cameraData = "{$camera->address}:{$camera->port}";
            $url = str_replace("{camera_data}", $cameraData, Camera::CAMERA_SNAPSHOT_URL);
            try {
                $imageName = uniqid("Cam{$camera->id}_") . '.jpg';
                $imageContent = $camera->curlGet($url);
                $imagePath = Yii::getAlias(Camera::CAMERA_IMAGE_PATH) . $imageName;
                $this->stdout('Downloaded ' . strlen($imagePath) . ' bytes' . PHP_EOL);
                $result = file_put_contents($imagePath, $imageContent);
                if ($result > 0) {
                    $camera->image = $imageName;
                    $camera->save();
                    $this->stdout("Image has been updated" . PHP_EOL);
                } else {
                    $this->stdout("No data saved" . PHP_EOL);
                }
            } catch (\Exception $e) {
                $this->stdout($e->getMessage() . PHP_EOL);
            }
        } else {
            $this->stdout('Camera not found' . PHP_EOL);
        }
    }

    /**
     * Runs web socket server
     */
    public function actionStartWsServer() {
        /*
          $server = IoServer::factory(new HttpServer(new WsServer(new LogStream())), 8080);
          $server->run();
         */
        $loop = \React\EventLoop\Factory::create();
        $pusher = new LogStream();
        // Listen for the web server to make a ZeroMQ push after an ajax request
        $context = new \React\ZMQ\Context($loop);
        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
        $pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
        $pull->on('message', array($pusher, 'onLogEntry'));
        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new \React\Socket\Server($loop);
        $webSock->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new \Ratchet\Server\IoServer(
                new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                new \Ratchet\Wamp\WampServer(
                $pusher
                )
                )
                ), $webSock
        );

        $loop->run();
    }

    /**
     * Displays current agent list 
     */
    public function actionWaitList() {
        $memcache = new \Memcache();
        $memcache->connect('localhost', 11211) or die("Could not connect");
        $activeUserArr = $memcache->get(LogStream::MEMCACHE_WATCH_WAIT_LIST);
        var_dump($activeUserArr);
    }

    /**
     * Set wait list as empty array
     */
    public function actionEmptyWaitList() {
        $memcache = new \Memcache();
        $memcache->connect('localhost', 11211) or die("Could not connect");
        $activeUserArr = [];
        $memcache->set(LogStream::MEMCACHE_WATCH_WAIT_LIST, $activeUserArr);
    }

    public function actionTest() {
        
        
        $property = Property::findOne(1);
        LogStream::getSocketConnection()->send(LogStream::getPropertyWatchJson($property->id, ['logUpdate' => true, 'pin' => [
                        'required' => $property->propertyAccessLog->getTimePassed(false) > PropertyAccessLog::PIN_TYPING_INTERVAL,
                        'status' => $property->propertyAccessLog->check_result,
                        'time' => $property->propertyAccessLog->getTimePassed(),
                        'timepassed' => $property->propertyAccessLog->getTimePassed(false)
        ]]));

        $logName = \common\models\Log::LOG_API_CAMERY_TYPING;
        \common\models\Log::add($logName, '{}', null, null, $property->id);
        
        return "";

        //LogStream::getSocketConnection()->send(LogStream::getPropertyWatchJson('1', ['pinTyping' => true]));
        //LogStream::getSocketConnection()->send(LogStream::getPropertyWatchJson('1', ['logUpdate' => true]));
        //LogStream::getSocketConnection()->send(LogStream::getPropertyWatchJson('1', ['pinTyping' => true]));



        $property = Property::findOne(1);
        /*
          PropertyAccessLog::addAccessLog($property->id, true, '123');
          LogStream::getSocketConnection()->send(LogStream::getPropertyWatchJson('1', ['logUpdate' => true, 'pin' => [
          'required' => $property->propertyAccessLog->getTimePassed(false) > PropertyAccessLog::PIN_TYPING_INTERVAL,
          'status' => $property->propertyAccessLog->check_result,
          'time' => $property->propertyAccessLog->getTimePassed(),
          'timepassed' => $property->propertyAccessLog->getTimePassed(false)
          ]]));
         */
        $comment = \common\models\Comment::findOne(1);
        $html = Html::tag('div', Html::tag('div', Html::tag('div', Html::tag('div', Html::tag('i', '', ['class' => 'fa fa-user']), ['class' => 'info-box-icon bg-gray'])
                                        , ['class' => 'text-center']) . Html::tag('div', ucfirst($comment->user->username), [])
                                , ['class' => 'col-sm-2']) .
                        Html::tag('div', Html::tag('div', Yii::$app->formatter->asDatetime($comment->created_at)) .
                                Html::tag('div', $comment->message, ['class' => 'well text-left'])
                                , ['class' => 'col-sm-10'])
                        , ['class' => 'row margin-top-block-md']);
        LogStream::getSocketConnection()->send(LogStream::getPropertyWatchJson('1', ['comment' => [
                        'html' => $html
        ]]));

        //$alertID = Alert::add('1J01263PAX00044', '127.0.0.1', Alert::EVENT_VIDEO_MOTION);
        //if ($alertID && ($alert = Alert::findOne($alertID)))
        //    $alert->assignWSAlertUsingMemcache();
        //$tmp = json_decode(LogStream::getWatchNotificationJson('6', '1', '195'), true);
        //var_dump($tmp);
        //return;
        //  Retrieve online user ids
    }

}
