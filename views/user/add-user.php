<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JqueryAsset;

use common\components\OcHelper;

/** @var yii\web\View $this */
/** @var frontend\models\CrsCounselor $model */

$this->title = 'Add User';
$this->params['breadcrumbs'][] = ['label' => 'Counselors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'New User', 'url' => ['create']];
$this->params['breadcrumbs'][] = $this->title;

$staffPic=OcHelper::getStaffPicture($_GET['staff_no']);
?>
<div class="crs-counselor-create">

    <!-- <h1>?= Html::encode($this->title) ?></h1> -->

    <div class="card" id="card-title-2">
        <div class="card-header">
            <h5 class="card-title"><?= Html::encode($this->title) ?></h5>
        </div>

        <div class="card-body">

            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <!-- Left Column - Profile Image -->
                <div class="col-md-3 text-center border-end">
                    <div class="mb-4">
                        <?= Html::img($staffPic, [
                            'alt' => 'Profile Image',
                            'class' => 'img-thumbnail rounded-circle mb-3',
                            'style' => 'width: 150px; height: 150px; object-fit: cover;'
                        ]) ?>
                        <h5 class="mb-1"><?= ($staff->sm_title ? ucwords(strtolower($staff->sm_title)) : null) . ($staff->sm_staff_name ? ' ' . ucwords(strtolower($staff->sm_staff_name)) : null) ?></h5>
                    </div>
                </div>

                <!-- Right Column - Profile Details -->
                <div class="col-md-9">
                    <div class="row g-4">
                        <!-- Personal Information Section -->
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Personal Information</h6>
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="profile-field">
                                        <label class="text-muted small">Staff No</label>
                                        <div class="fs-6"><?= $staff->sm_staff_id ? $staff->sm_staff_id : '' ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-field">
                                        <label class="text-muted small">Name</label>
                                        <div class="fs-6"><?= $staff->sm_staff_name ? $staff->sm_staff_name : '' ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-field">
                                        <label class="text-muted small">Username</label>
                                        <div class="fs-6"><?= $staff->sm_email_addr ? explode('@', $staff->sm_email_addr)[0] : '' ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-field">
                                        <label class="text-muted small">Email</label>
                                        <div class="fs-6"><?= $staff->sm_email_addr ? $staff->sm_email_addr : '' ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-field">
                                        <label class="text-muted small">Location</label>
                                        <div class="fs-6"><?= $staff->campus_desc ? $staff->campus_desc : '' ?></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?= $form->field($model, 'status')->hiddenInput(['value'=> '10'])->label(false); ?>

            <div class="form-group">
                <?= Html::submitButton('Add User', [
                    'class' => 'btn btn-success',
                    'data' => [
                        'confirm' => 'Are you sure you want to add this?',
                        'method' => 'post'
                    ]
                ]) ?>
                <?= Html::a('Back', ['create'], ['class' => 'btn btn-warning']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

    </div>

</div>
