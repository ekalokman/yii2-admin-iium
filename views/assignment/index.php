<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel mdm\admin\models\searchs\Assignment */
/* @var $usernameField string */
/* @var $extraColumns string[] */

$this->title = Yii::t('rbac-admin', 'Assignments');
$this->params['breadcrumbs'][] = $this->title;

// Register custom CSS for pagination
$css = <<<CSS
.pagination {
    margin-top: 20px;
    margin-bottom: 20px;
}

.pagination .page-item {
    display: inline-block;
}

/* Force borders on ALL page links including navigation buttons */
.pagination .page-link,
.pagination .page-item .page-link,
.pagination li a,
.pagination li span {
    padding: 10px 15px !important;
    font-size: 16px !important;
    font-weight: 500 !important;
    color: #333 !important;
    border: 1px solid #ddd !important;
    margin: 0 3px !important;
    border-radius: 5px !important;
    background-color: #fff !important;
    display: inline-block !important;
    min-width: 45px !important;
    text-align: center !important;
    box-sizing: border-box !important;
}

.pagination .page-item.active .page-link,
.pagination .page-item.active a,
.pagination .page-item.active span,
.pagination li.active a,
.pagination li.active span {
    background-color: var(--primary) !important;
    border-color: var(--primary) !important;
    color: #fff !important;
    font-weight: bold !important;
}

/* Fallback if CSS variables not available */
.pagination .page-item.active .page-link,
.pagination .page-item.active a {
    background-color: #3b5998 !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-color: #667eea !important;
}

.pagination .page-link:hover,
.pagination .page-item a:hover {
    background-color: #f8f9fa !important;
    border-color: #aaa !important;
    color: #000 !important;
}

.pagination .page-item.disabled .page-link,
.pagination .page-item.disabled a,
.pagination .page-item.disabled span {
    color: #ccc !important;
    background-color: #f5f5f5 !important;
    border-color: #ddd !important;
    cursor: not-allowed !important;
}

/* Specifically target first and last navigation buttons */
.pagination .page-item:first-child .page-link,
.pagination .page-item:first-child a,
.pagination .page-item:last-child .page-link,
.pagination .page-item:last-child a,
.pagination li:first-child a,
.pagination li:last-child a {
    border: 1px solid #ddd !important;
    background-color: #fff !important;
}

/* Remove any border-radius override on first/last items */
.pagination .page-item:first-child .page-link,
.pagination .page-item:last-child .page-link {
    border-radius: 5px !important;
}

.summary {
    padding: 10px 0;
    font-size: 14px;
    color: #666;
}
CSS;
$this->registerCss($css);

// Define separate columns for Staff
$columnsStaff = [
    ['class' => 'yii\grid\SerialColumn'],
    $usernameField,
    [
        'attribute' => 'sm_staff_name',
        'value' => function($model) {
            return $model->sm_staff_name;
        },
        'label' => 'Name',
    ],
    // [
    //     'attribute' => 'sm_staff_name',
    //     'value' => 'staff.sm_staff_name',
    //     'label' => 'Name',
    //     'filterInputOptions' => [
    //         'class' => 'form-control grid-filter',
    //         'placeholder' => 'Enter for Search...',
    //     ],
    // ],
];

// Define separate columns for Student
$columnsStudent = [
    ['class' => 'yii\grid\SerialColumn'],
    $usernameField,
    [
        'value' => 'student.studentname',
        'label' => 'Name',
    ],
    // [
    //     'attribute' => 'name',
    //     'value' => 'student.name',
    //     'label' => 'Name',
    //     'filterInputOptions' => [
    //         'class' => 'form-control grid-filter',
    //         'placeholder' => 'Enter for Search...',
    //     ],
    // ],
];

if (!empty($extraColumns)) {
    $columnsStaff = array_merge($columnsStaff, $extraColumns);
    $columnsStudent = array_merge($columnsStudent, $extraColumns);
}

$columnsStaff[] = [
    'class' => 'yii\grid\ActionColumn',
    'template' => '{view}'
];

$columnsStudent[] = [
    'class' => 'yii\grid\ActionColumn',
    'template' => '{view}'
];
?>

<div class="assignment-index">
    <div class="col-xl-12">
        <div class="card dz-card" id="bootstrap-table3">
            <div class="card-header flex-wrap d-flex justify-content-between border-0">
                <div>
                    <h4 class="card-title"><?= Html::encode($this->title) ?></h4>
                </div>
            </div>

            <div class="tab-content" id="myTabContent-2">
                <div class="tab-pane fade show active" id="withoutSpace" role="tabpanel" aria-labelledby="home-tab-2">
                    <div class="card-body pt-0">
                        <!-- Nav tabs -->
                        <div class="custom-tab-1">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#staff">
                                        <i class="la la-user me-2"></i> Staff
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#student">
                                        <i class="la la-user me-2"></i> Student
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <!-- Staff Tab -->
                                <div class="tab-pane fade show active" id="staff" role="tabpanel">
                                    <div class="pt-4">
                                        <h4>Staff</h4>

                                        <?php Pjax::begin(); ?>
                                            <?= GridView::widget([
                                                'dataProvider' => $dataProviderStaff,
                                                'filterModel' => $searchModel,
                                                'tableOptions' => ['class' => 'table'],
                                                'columns' => $columnsStaff, // Using separate columns for staff
                                                'pager' => [
                                                    'class' => LinkPager::class,
                                                    'options' => ['class' => 'pagination pagination-primary'],
                                                    'linkOptions' => ['class' => 'page-link'],
                                                    'activePageCssClass' => 'active',
                                                    'disabledPageCssClass' => 'disabled',
                                                    'prevPageLabel' => '<i class="la la-angle-left"></i>',
                                                    'nextPageLabel' => '<i class="la la-angle-right"></i>',
                                                    'firstPageLabel' => '<i class="la la-angle-double-left"></i>',
                                                    'lastPageLabel' => '<i class="la la-angle-double-right"></i>',
                                                ],
                                            ]); ?>
                                        <?php Pjax::end(); ?>
                                        
                                    </div>
                                </div>

                                <!-- Student Tab -->
                                <div class="tab-pane fade" id="student">
                                    <div class="pt-4">
                                        <h4>Student</h4>

                                        <?php Pjax::begin(); ?>
                                            <?= GridView::widget([
                                                'dataProvider' => $dataProviderStudent,
                                                'filterModel' => $searchModel,
                                                'tableOptions' => ['class' => 'table'],
                                                'columns' => $columnsStudent, // Using separate columns for student
                                                'pager' => [
                                                    'class' => LinkPager::class,
                                                    'options' => ['class' => 'pagination pagination-primary'],
                                                    'linkOptions' => ['class' => 'page-link'],
                                                    'activePageCssClass' => 'active',
                                                    'disabledPageCssClass' => 'disabled',
                                                    'prevPageLabel' => '<i class="la la-angle-left"></i>',
                                                    'nextPageLabel' => '<i class="la la-angle-right"></i>',
                                                    'firstPageLabel' => '<i class="la la-angle-double-left"></i>',
                                                    'lastPageLabel' => '<i class="la la-angle-double-right"></i>',
                                                ],
                                            ]); ?>
                                        <?php Pjax::end(); ?>
                                        
                                    </div>
                                </div>
                            </div> <!-- End Tab Content -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$resetUrl = Url::to(['/admin/assignment']);

$js = <<<JS

$(document).ready(function () {
    // Check if there's an active tab stored
    var activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
    }

    // When a tab is clicked, store the active tab in localStorage
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        var tabId = $(e.target).attr('href');
        localStorage.setItem('activeTab', tabId);
    });
});

$(document).ready(function() {
    $('.grid-filter').each(function() {
        var input = $(this);
        var clearBtn = $('<span class="clear-input">✖</span>');

        // Position the clear button inside the input field
        input.after(clearBtn);
        input.parent().css('position', 'relative');
        clearBtn.css({
            position: 'absolute',
            right: '15px',
            top: '50%',
            transform: 'translateY(-50%)',
            cursor: 'pointer',
            color: '#999'
        });

        // Show the clear button if input already has value (after search)
        if (input.val().length > 0) {
            clearBtn.show();
        } else {
            clearBtn.hide();
        }

        // Show clear button when typing
        input.on('input', function() {
            clearBtn.toggle($(this).val().length > 0);
        });

        // When clicking the clear button, redirect to the custom reset URL
        clearBtn.on('click', function() {
            window.location.href = '$resetUrl'; // Redirect to reset URL
        });
    });
});

JS;
$this->registerJs($js);
?>
