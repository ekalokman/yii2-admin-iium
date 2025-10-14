<?php

namespace mdm\admin\models;

use mdm\admin\components\Configs;
use mdm\admin\components\UserStatus;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 *
 * @property UserProfile $profile
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;

    public $sm_staff_name; // Virtual property for staff name from JOIN
    public $name; // Virtual property for student name from JOIN

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Configs::instance()->userTable;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'in', 'range' => [UserStatus::ACTIVE, UserStatus::INACTIVE]],
            [['sm_staff_name', 'name'], 'safe'], // Allow mass assignment from JOIN
        ];
    }

    /**
     * Override populateRecord to handle extra columns from JOINs
     */
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        // Manually set extra columns that aren't in the table schema
        if (isset($row['sm_staff_name'])) {
            $record->sm_staff_name = $row['sm_staff_name'];
        }
        if (isset($row['name'])) {
            $record->name = $row['name'];
        }
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

        // Populate virtual properties from query results if they exist
        // This is needed when extra columns are selected via JOIN
        $attrs = $this->getOldAttributes();

        if (!$this->sm_staff_name && isset($attrs['sm_staff_name'])) {
            $this->sm_staff_name = $attrs['sm_staff_name'];
        }

        if (!$this->name && isset($attrs['name'])) {
            $this->name = $attrs['name'];
        }
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => UserStatus::ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => UserStatus::ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                'password_reset_token' => $token,
                'status' => UserStatus::ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public static function getDb()
    {
        return Configs::userDb();
    }

    /**
     * Get staff relation
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        // Join staff by email OR by constructing email from username
        // Staff usernames are typically just the staff ID (e.g., 'hairunnaja')
        // and their email in staff table is 'hairunnaja@iium.edu.my'

        // Use hasOne with primary link by email
        // The OR condition for username@iium.edu.my is handled in joinWith() queries
        return $this->hasOne(\common\models\Staff::className(), ['sm_email_addr' => 'email'])
            ->andWhere(['ad_counseling.view_staff_biodata.sm_staff_status' => '1']);
    }

    /**
     * Get student relation
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(\common\models\Student::className(), ['matno' => 'username']);
    }
}
