<?php

namespace common\models;

use Yii;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use common\models\User;

class LogStream implements WampServerInterface {

    const MEMCACHE_WATCH_WAIT_LIST = 'watch_wait_list';

    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    /**
     * All connections array
     */
    protected $connections = array();

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()] = $topic;

        //  Add active agents to the wait stack
        if (preg_match('/user_(\d+)_watch_wait/', $topic->getId(), $matches)) {
            if (($userId = $matches[1])) {
                $connectionId = $conn->WAMP->sessionId;
                $memcache = new \Memcache();
                $memcache->connect('localhost', 11211) or die("Could not connect");
                $activeUserArr = $memcache->get(self::MEMCACHE_WATCH_WAIT_LIST);
                $activeUserArr = $this->checkSessions($activeUserArr); //   Check sessions statuses
                if (!isset($activeUserArr) || !is_array($activeUserArr))
                    $activeUserArr = [];
                $activeUserArr[$userId][] = $connectionId;
                $memcache->set(self::MEMCACHE_WATCH_WAIT_LIST, $activeUserArr);
            }
        }
        if (preg_match('/property_(\d+)_watch/', $topic->getId(), $matches)) {
            $topic->broadcast(['params' => [ 'connectionCount' => $topic->count()]]);
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
        if (preg_match('/property_(\d+)_watch/', $topic->getId(), $matches)) {
            $topic->broadcast(['params' => [ 'connectionCount' => $topic->count()]]);
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->connections[$conn->WAMP->sessionId] = $conn;
    }

    public function onClose(ConnectionInterface $conn) {
        //  Remove offline agents from the wait stack
        foreach ($this->subscribedTopics as $topic) {
            if (preg_match('/user_(\d+)_watch_wait/', $topic->getId(), $matches)) {
                if (($userId = $matches[1])) {
                    //  Add active agents to the wait stack
                    $connectionId = $conn->WAMP->sessionId;
                    $memcache = new \Memcache();
                    $memcache->connect('localhost', 11211) or die("Could not connect");
                    $activeUserArr = $memcache->get(self::MEMCACHE_WATCH_WAIT_LIST);
                    $activeUserArr = $this->checkSessions($activeUserArr); //   Check sessions statuses
                    if (!isset($activeUserArr) || !is_array($activeUserArr))
                        $activeUserArr = [];
                    if (isset($activeUserArr[$userId]) && is_array($activeUserArr[$userId]) && ($connectionKey = array_search($connectionId, $activeUserArr[$userId])) !== false) {
                        if (isset($activeUserArr[$userId][$connectionKey]))
                            unset($activeUserArr[$userId][$connectionKey]);
                        if (count($activeUserArr[$userId]) === 0)
                            unset($activeUserArr[$userId]);
                    }
                    $memcache->set(self::MEMCACHE_WATCH_WAIT_LIST, $activeUserArr);
                    return;
                }
            }
        }
        //  Remove from connections list 
        unset($this->connections[$conn->WAMP->sessionId]);
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        switch ($topic->getId()) {
            case "readyToWork":
                $userId = $params['userId'];
                $connectionId = $conn->WAMP->sessionId;
                $memcache = new \Memcache();
                $memcache->connect('localhost', 11211) or die("Could not connect");
                $activeUserArr = $memcache->get(self::MEMCACHE_WATCH_WAIT_LIST);
                if (!isset($activeUserArr) || !is_array($activeUserArr))
                    $activeUserArr = [];
                $activeUserArr[$userId][] = $connectionId;
                $memcache->set(self::MEMCACHE_WATCH_WAIT_LIST, $activeUserArr);
                return json_encode(['result' => true]);
            /*
              case "waitAlert":
              $entryData = json_decode($params, true);
              $topic->broadcast(LogStream::getUserNotificationJson("6", "Test", "Message " . $entryData['user_id'], "info"));
              return $entryData['user_id'];
             */
        }
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        $log = $event;
        $topic->broadcast(json_encode($log));
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        
    }

    /**
     * Broadcast message to the required category of subscription
     * @param string $entry JSON'ified string we'll receive from ZeroMQ. Must have {'category': '', 'connection_id': []} to determine subscription, connection_id - eligible id array
     * @return type
     */
    public function onLogEntry($entry) {
        $entryData = json_decode($entry, true);
        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($entryData['category'], $this->subscribedTopics)) {
            return;
        }
        if (isset($entryData['category']) && isset($this->subscribedTopics[$entryData['category']])) {
            $topic = $this->subscribedTopics[$entryData['category']];
            if (isset($entryData['connection_id']) && $entryData['connection_id']) {
                $eligibleIdArr = $entryData['connection_id'];
                if (!is_array($eligibleIdArr))
                    $eligibleIdArr = [$eligibleIdArr];
                $topic->broadcast($entryData, [], $eligibleIdArr);
            } else {
                // re-send the data to all the clients subscribed to that category
                $topic->broadcast($entryData);
            }
        }
    }

    /**
     * Sends JS object to WS stream
     * 
     * @param type $message
     */
    public static function sendMessage($message) {
        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");
        $socket->send($message);
    }

    /**
     * Returns user watch notification json
     * 
     * @param type $userId
     * @param type $alertId
     * @return string
     */
    public static function getWatchNotificationJson($userId, $alertId, $connectionId = null) {
        if ($connectionId)
            return json_encode(['category' => "user_{$userId}_watch_wait", 'result' => true, 'alert_id' => $alertId, 'connection_id' => [$connectionId]]);
        else
            return json_encode(['category' => "user_{$userId}_watch_wait", 'result' => true, 'alert_id' => $alertId]);
    }
	
	public static function getTagLogJson($authorised, $notauthorised) {
		return json_encode(['category' => "find_tag_log", 'result' => true, 'alert_id' => ['authorised' => $authorised, 'notauthorised' => $notauthorised]]);
    }

    /**
     * Returns system notification json
     * 
     * @param type $userId
     * @param type $title
     * @param type $message
     * @param type $type
     * @return string
     */
    public static function getUserNotificationJson($userId, $title, $message, $type) {
        return json_encode([
            'category' => "user_{$userId}_notification",
            'notificationTitle' => $title,
            'notificationMessage' => $message,
            'notificationType' => $type
        ]);
    }

    /**
     * Walks throught the active user array and chech if the are still active 
     * 
     * @param type $sessionArray
     * @return Array filtered array
     */
    protected function checkSessions($sessionArray) {
        $resultArr = $sessionArray;
        foreach ($sessionArray as $userId => $userArray) {
            foreach ($userArray as $id => $sessionId) {
                if (!key_exists($sessionId, $this->connections)) {
                    unset($resultArr[$userId][$id]);
                    if (count($resultArr[$userId]) === 0)
                        unset($resultArr[$userId]);
                }
            }
        }
        return $resultArr;
    }

    /**
     * Returns message to push to user property watch connection
     * 
     * @param type $propertyId
     * @param type $params
     * @return Json string
     */
    public static function getPropertyWatchJson($propertyId, $params) {
        return json_encode(['category' => "property_{$propertyId}_watch", 'params' => $params]);
    }

    /**
     * Returns socket connection
     * 
     * @return \React\ZMQ\SocketWrapper
     */
    public static function getSocketConnection() {
        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");
        return $socket;
    }

}
