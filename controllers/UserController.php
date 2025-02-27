<?php

namespace mdm\admin\controllers;

use mdm\admin\components\UserStatus;
use mdm\admin\models\form\ChangePassword;
use mdm\admin\models\form\Login;
use mdm\admin\models\form\PasswordResetRequest;
use mdm\admin\models\form\ResetPassword;
use mdm\admin\models\form\Signup;
use mdm\admin\models\searchs\User as UserSearch;
use mdm\admin\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\base\UserException;
use yii\filters\VerbFilter;
use yii\mail\BaseMailer;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\data\Pagination;

use common\models\AuthItem;
use common\models\Staff;
use common\models\UserAuth;

/**
 * User controller
 */
class UserController extends Controller
{
    private $_oldMailPath;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'logout' => ['post'],
                    'activate' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (Yii::$app->has('mailer') && ($mailer = Yii::$app->getMailer()) instanceof BaseMailer) {
                /* @var $mailer BaseMailer */
                $this->_oldMailPath = $mailer->getViewPath();
                $mailer->setViewPath('@mdm/admin/mail');
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        if ($this->_oldMailPath !== null) {
            Yii::$app->getMailer()->setViewPath($this->_oldMailPath);
        }
        return parent::afterAction($action, $result);
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
                'model' => $this->findModel($id),
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Login
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->getUser()->isGuest) {
            return $this->goHome();
        }

        $model = new Login();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                    'model' => $model,
            ]);
        }
    }

    /**
     * Logout
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->getUser()->logout();

        return $this->goHome();
    }

    /**
     * Signup new user
     * @return string
     */
    public function actionSignup()
    {
        $model = new Signup();
        if ($model->load(Yii::$app->getRequest()->post())) {
            if ($user = $model->signup()) {
                return $this->goHome();
            }
        }

        return $this->render('signup', [
                'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new User();

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Request reset password
     * @return string
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequest();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
                'model' => $model,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPassword($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
                'model' => $model,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->change()) {
            return $this->goHome();
        }

        return $this->render('change-password', [
                'model' => $model,
        ]);
    }

    /**
     * Activate new user
     * @param integer $id
     * @return type
     * @throws UserException
     * @throws NotFoundHttpException
     */
    public function actionActivate($id)
    {
        /* @var $user User */
        $user = $this->findModel($id);
        if ($user->status == UserStatus::INACTIVE) {
            $user->status = UserStatus::ACTIVE;
            if ($user->save()) {
                return $this->goHome();
            } else {
                $errors = $user->firstErrors;
                throw new UserException(reset($errors));
            }
        }
        return $this->goHome();
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionSearch($query)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // Ensure JSON response
    
        try {
            $query = trim($query); // Trim the search query
    
            // Build the query to search for staff
            $staffQuery = Staff::find()
                ->where(['sm_staff_id' => $query])
                ->orWhere(['ilike', 'sm_staff_name', $query])
                ->andWhere(['computed_status_desc' => 'Active']) // Add condition for current_status is active
                ->andWhere(['IS NOT', 'sm_email_addr', null]); // Check not null
    
            // Pagination setup
            $pagination = new Pagination([
                'totalCount' => $staffQuery->count(),
                'pageSize' => 10, // Display 10 records per page
            ]);
    
            // Fetch the data with pagination
            $staff = $staffQuery->offset($pagination->offset)
                ->limit($pagination->limit)
                ->asArray()
                ->all();
    
            // Return the data along with pagination links
            return [
                'staff' => $staff,
                'pagination' => [
                    'totalCount' => $pagination->totalCount,
                    'pageCount' => $pagination->getPageCount(),
                    'currentPage' => $pagination->getPage() + 1, // Yii2 pagination is 0-based
                ],
            ];
        } catch (\Exception $e) {
            // Log the error and return a JSON error response
            \Yii::error('Error in actionSearch: ' . $e->getMessage());
            return [
                'error' => 'An error occurred while processing your request.',
            ];
        }
    }

        /**
     * Check if user exists
     * @param string $username username
     * @return array
     */
    public function actionCheckUser($staff_no)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $staffEmail = Staff::find()->select(['sm_email_addr'])->where(['sm_staff_id' => $staff_no])->scalar();

        $emailParts = explode('@', $staffEmail);
        $ssoid = $emailParts[0] ?? '';
        
        $exists = User::find()->where(['username' => $ssoid])->exists();
        
        return ['exists' => $exists];
    }

        /**
     * Add a new user.
     * @param string $staff_no Staff No
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the staff is not found
     */
    public function actionAddUser($staff_no)
    {
        $staff = Staff::findOne(['sm_staff_id' => $staff_no]);
        if (!$staff) {
            throw new NotFoundHttpException('Staff not found.');
        }

        $emailParts = explode('@', $staff->sm_email_addr);
        $ssoid = $emailParts[0] ?? '';

        // Check if the user already exists
        $existingUser = User::findOne(['username' => $ssoid]);
        if ($existingUser) {
            Yii::debug("User already exists: " . $existingUser->id);
            return $this->render('add-user', [
                'staff' => $staff,
                'model' => $existingUser,
            ]);
        }

        // If user doesn't exist, show the form
        $model = new User();
        $model->username = $ssoid;
        $model->email = $staff->sm_email_addr;
        $model->usertype = 'STF';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->created_at = time();
            $model->generateAuthKey();
            
            if ($model->save(false)) { // Disable validation to avoid duplicate validation
                Yii::debug('User created: ' . $model->id);
                return $this->redirect(['assignment/view', 'id' => $model->id]);
            } else {
                Yii::error('Failed to create user: ' . print_r($model->errors, true));
                throw new \Exception('Failed to create user: ' . print_r($model->errors, true));
            }
        }

        return $this->render('add-user', [
            'staff' => $staff,
            'model' => $model,
        ]);
    }

}
