<?php

namespace common\models;

use Yii;
use \GuzzleHttp\Client;

/**
 * This is the model class for table "camera".
 *
 * @property integer $id
 * @property string $name
 * @property string $address
 * @property string $port
 * @property string $login
 * @property string $password
 * @property string $serial_number
 * @property integer $property_id
 * @property string $image
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $deleted_at
 * @property Property $property
 * @property string $auth_type
 * @property CameraLog $cameraLogs
 */
class Camera extends ARModel {

    /**
     * Path to the image directory
     */
    const CAMERA_IMAGE_PATH = "@app/../files/img/camera/";

    /**
     * Url to access camera image
     */
    const CAMERA_SNAPSHOT_URL = "http://{camera_data}/cgi-bin/snapshot.cgi?channel=0";

    /**
     * Basic auth type
     */
    const CAMERA_AUTH_TYPE_BASIC = "basic";

    /**
     * Digest auth type
     */
    const CAMERA_AUTH_TYPE_DIGEST = "digest";

    /**
     * Array of events which fire an alert
     * @var type 
     */
    protected static $alerEventArr = ['Tripwire', 'Mail_Test', 'Motion Detection', 'CrossLineDetection'];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'camera';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        $rulesArr = [
            [['property_id'], 'default', 'value' => 0],
            [['serial_number', 'image'], 'default', 'value' => ''],
            [['auth_type'], 'default', 'value' => self::CAMERA_AUTH_TYPE_DIGEST],
            [['serial_number'], 'unique'],
            [['name', 'address', 'port', 'login', 'password', 'property_id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'login', 'password', 'serial_number', 'image'], 'string', 'max' => 50],
            [['address'], 'string', 'max' => 100],
            [['auth_type'], 'string', 'max' => 20],
            [['port'], 'string', 'max' => 5],
        ];

        return array_merge($rulesArr, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'address' => 'Address',
            'port' => 'Port',
            'login' => 'Login',
            'password' => 'Password',
            'serial_number' => 'Serial Number',
            'property_id' => 'Property',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Returns URL of RTSP thread
     * $type - thread number: 0 - Hight quality, 1 - low qality
     * @return string
     */
    public function getUrlRTSP($type = 1) {
        return "rtsp://{$this->login}:{$this->password}@{$this->address}:{$this->port}/cam/realmonitor?channel=1&subtype=$type";
    }

    /**
     * Returns URL of HTTP thread
     * 
     * @param type $type
     * @return string
     */
    public function getUrlHttp($type = 1) {
        return "http://{$this->login}:{$this->password}@{$this->address}:{$this->port}/cgi-bin/mjpg/video.cgi?subtype=$type";
        //return "http://{$this->login}:{$this->password}@{$this->address}:{$this->port}/cgi-bin/realmonitor.cgi?action=getStream&channel=1&subtype=$type";
    }

    /**
     * Return associated record
     * @return Property
     */
    public function getProperty() {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }

    /**
     * Returns the path to the latest image
     * @return string
     */
    public function getImagePath() {
        if ($this->image)
            return \yii\helpers\Url::to(['camera/image', 'id' => $this->id, 'ts' => time()]);
        else
            return "/img/camera.png";
    }

    /**
     * Returns the content of latest image
     */
    public function getImageContent() {
        if ($this->image) {
            $imagePath = Yii::getAlias(self::CAMERA_IMAGE_PATH) . $this->image;
            if (file_exists($imagePath))
                return file_get_contents($imagePath);
            return "";
        } else
            return "";
    }

    /**
     * Downloads current camera image
     * @return boolean
     */
    public function downloadImage() {
        $url = $this->urlReplaceCameraData(self::CAMERA_SNAPSHOT_URL);
        try {
            /*
              $contextArr = [
              'http' => [
              'method' => "GET",
              'header' => 'Authorization: Basic ' . base64_encode("{$this->login}:{$this->password}")
              ]
              ];
              $context = stream_context_create($contextArr);
              $imageName = uniqid("Cam{$this->id}_");
              $imageContent = $this->getDataByCurl($url);
             */

            $imageName = uniqid("Cam{$this->id}_") . '.jpg';
            $imageContent = $this->curlGet($url);

            $imagePath = Yii::getAlias(self::CAMERA_IMAGE_PATH) . $imageName;
            if ($imageContent) {
                $result = file_put_contents($imagePath, $imageContent);
            } else {
                $result = 0;
            }
            if ($result > 0) {
                $this->image = $imageName;
                return $this->save();
            } else {
                //  Try to use basic auth type
                $imageContent = $this->curlGet($url);
                $result = file_put_contents($imagePath, $imageContent);
                if ($result > 0) {
                    $this->image = $imageName;
                    return $this->save();
                }
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reboots the camera and return result
     * @return type
     */
    public function apiReboot() {
        $url = 'http://{camera_data}/cgi-bin/magicBox.cgi?action=reboot';
        $url = $this->urlReplaceCameraData($url);
        $response = $this->curlGet($url);
        return ($response == "OK");
    }
    
    /**
     * Returns device type or null (if no possible to retrieve)
     * 
     * @return type
     */
    public function apiGetDeviceType() {
        $url = 'http://{camera_data}/cgi-bin/magicBox.cgi?action=getDeviceType';
        $url = $this->urlReplaceCameraData($url);
        $response = $this->curlGet($url);
        $responseArr = explode('=', $response);
        return (count($responseArr) === 2 ? $responseArr[1] : null);
    }
    
    /**
     * Returns Serial No or null (if no possible to retrieve)
     * 
     * @return type
     */
    public function apiGetSerialNo() {
        $url = 'http://{camera_data}/cgi-bin/magicBox.cgi?action=getSerialNo';
        $url = $this->urlReplaceCameraData($url);
        $response = $this->curlGet($url);
        $responseArr = explode('=', $response);
        return (count($responseArr) === 2 ? $responseArr[1] : null);
    }
    
    /**
     * Returns getSoftwareVersion or null (if no possible to retrieve)
     * 
     * @return type
     */
    public function apiGetSoftwareVersion() {
        $url = 'http://{camera_data}/cgi-bin/magicBox.cgi?action=getSoftwareVersion';
        $url = $this->urlReplaceCameraData($url);
        $response = $this->curlGet($url);
        $responseArr = explode('=', $response);
        return (count($responseArr) === 2 ? $responseArr[1] : null);
    }
    

    /**
     * Returns response if query
     * 
     * @param type $url
     * @param type $authType
     * @return type
     */
    public function curlGet($url) {
        $curl = \curl_init();
        \curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        if ($this->auth_type === self::CAMERA_AUTH_TYPE_DIGEST) {
            \curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                //CURLOPT_VERBOSE => true,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => TRUE,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
                CURLOPT_USERPWD => "$this->login:$this->password",
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 2,
            ));
        } elseif ($this->auth_type === self::CAMERA_AUTH_TYPE_BASIC) {
            \curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 2,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0',
                CURLOPT_URL => $url,
                CURLOPT_FOLLOWLOCATION => TRUE,
                CURLOPT_USERPWD => "$this->login:$this->password",
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 2,
            ));
        }
        $response = \curl_exec($curl);
        \curl_close($curl);
        return $response;
    }

    /**
     * Downloads content using curl
     * @param type $url
     * @return string
     */
    private function getDataByCurl($url) {
        try {

            $client = new Client([
                'base_uri' => $url,
                'timeout' => 5.0,
            ]);

            $response = $client->request('GET', $url, [
                'auth' => ['admin', 'admin', 'digest']
            ]);
            return $response->getBody();


            /*
              ob_start();
              $ch = curl_init();

              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_HEADER, true);
              curl_setopt($ch, CURLOPT_VERBOSE, true);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
              curl_setopt($ch, CURLOPT_POST, false);
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
              curl_setopt($ch, CURLOPT_USERPWD, $this->login . ":" . $this->password);
              curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
              curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');

              $output = curl_exec($ch);

              $info = curl_getinfo($ch);
              curl_close($ch);
              return $output;
             * 
             */
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Check whether event should fire an alert
     * @param type $eventName
     * @return type
     */
    public static function isAlertEvent($eventName) {
        return in_array($eventName, self::$alerEventArr);
    }

    /**
     * Replace in the given url all camera data parameters and returns final url for camera
     * 
     * @param type $url
     * @return type
     */
    private function urlReplaceCameraData($url) {
        $cameraData = "{$this->address}:{$this->port}";
        $urlResult = str_replace("{camera_data}", $cameraData, $url);
        return $urlResult;
    }

    /**
     * Returns all the camera logs
     * 
     * @return type
     */
    public function getCameraLogs(){
        return $this->hasMany(CameraLog::className(), ['camera_id' => 'id']);
    }
    
    /**
     * Retrieve status from last camera log if found or offlina otherwise
     * 
     * @return type
     */
    public function getStatus(){
        $lastLog = CameraLog::find()
                ->andWhere(['camera_id' => $this->id])
                ->orderBy(['id' => SORT_DESC])
                ->one();
        if($lastLog)
            return $lastLog->status;
        else
            return CameraLog::STATUS_OFFLINE;
                
    }
    
}
