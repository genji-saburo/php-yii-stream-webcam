<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class PasswordResetForm extends Model {

    public $password_old;
    public $password_new;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['password_new', 'password_old'], 'required'],
            ['password_new', 'validateSimbolsPass'],
            ['password_new', 'validatePasswordDuplication'],
            ['password_old', 'validatePassword'],
            ['password_old', 'string', 'min' => 6],
            ['password_new', 'string', 'min' => 6],
        ];
    }

    public function attributeLabels() {
        return [
            'password_new' => 'New password',
            'password_old' => 'Current password',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword() {
        if (!$this->hasErrors()) {
            $user = Yii::$app->user->identity;
            if (!$user || !$user->validatePassword($this->password_old)) {
                // $this->addError('password_old', 'Incorrect current password.');
            }
        }
    }

    public function validatePasswordDuplication() {
        if ($this->password_old == $this->password_new) {
            // $this->addError("password_new", "New password cannot be the same with current one.");
        }
    }

    /**
     * Проверяет поле на наличие толко символов английского и русского алфавита и спецсимволов
     * @param type $attribute
     * @param type $params
     */
    public function validateSimbolsPass($attribute, $params) {
        $password = $this->attributes[$attribute];
        if (!preg_match('/^[a-zA-Z0-9!@&#\$%\^\*\(\)_\-\.\+]+$/', $password)) {
            $this->addError($attribute, 'Password can contain only latin letters and signs: ! @ # & $ % ^ * () _ - .');
        }
    }

}
