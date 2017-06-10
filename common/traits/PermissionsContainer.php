<?php

namespace common\traits;

use common\models\User;

/**
 * Determines rules for the user roles
 */
trait PermissionsContainer {

    public static $CONTROLLER_ALERT = "alert";
    public static $CONTROLLER_CAMERA = "camera";
    public static $CONTROLLER_LOG = "log";
    public static $CONTROLLER_PROPERTY = "property";
    public static $CONTROLLER_SITE = "site";
    public static $CONTROLLER_USER = "user";

    /**
     * Check if action with provided alias is allowed
     * @param type $actionName
     * @return boolean
     */
    public function isAllowed($actionName, $controllerId = '') {
        if ($controllerId === '') {
            foreach ($this->getPermissions() as $curPermissionBlock) {
                foreach ($curPermissionBlock as $permissionKey => $permissionValue) {
                    if (strpos($actionName, $permissionKey) > 0) {
                        return $permissionValue;
                    }
                }
            }
        } else {
            $permissionArr = $this->getPermissions();
            if (is_array($permissionArr) && key_exists($controllerId, $permissionArr)) {
                foreach ($permissionArr[$controllerId] as $permissionKey => $permissionValue) {
                    $actionPosition = strpos($actionName, $permissionKey);
                    if ($actionPosition > 0 || $actionPosition === 0) {
                        return $permissionValue;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Returns array with permissions for the given role
     * @param type $roleId
     * @return Array
     */
    private function getPermissions($roleId = 0) {
        $permissions = [
            User::ROLE_AGENT => [
                self::$CONTROLLER_ALERT => [
                    'recieved' => true,
                ],
                self::$CONTROLLER_CAMERA => [
                    'image-refresh' => true,
                ],
                self::$CONTROLLER_LOG => [
                    'index' => true,
                    'view' => true,
                ],
                self::$CONTROLLER_PROPERTY => [
                ],
                self::$CONTROLLER_SITE => [
                    'index' => true,
                    'login' => true,
                    'logout' => true,
                    'password' => true,
                    'watch' => true,
                    'error' => true,
                ],
                self::$CONTROLLER_USER => [
                ],
            ],
        ];

        return (key_exists($roleId, $permissions) ? $permissions[$roleId] : $permissions[User::ROLE_AGENT] );
    }

}
