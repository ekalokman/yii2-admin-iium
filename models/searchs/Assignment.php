<?php

namespace mdm\admin\models\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Staff;

/**
 * AssignmentSearch represents the model behind the search form about Assignment.
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Assignment extends Model
{
    public $id;
    public $username;
    public $email;
    public $sm_staff_name;
    public $studentname;
    public $name; 

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'username', 'email', 'sm_staff_name', 'studentname', 'name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rbac-admin', 'ID'),
            'username' => Yii::t('rbac-admin', 'Username'),
            'name' => Yii::t('rbac-admin', 'Name'),
        ];
    }

    /**
     * Create data provider for Assignment model.
     * @param  array                        $params
     * @param  \yii\db\ActiveRecord         $class
     * @param  string                       $usernameField
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params, $class, $usernameField)
    {
        $query = $class::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', $usernameField, $this->username]);

        return $dataProvider;
    }

    /**
     * Create data provider for Assignment model.
     * @param  array                        $params
     * @param  \yii\db\ActiveRecord         $class
     * @param  string                       $usernameField
     * @return \yii\data\ActiveDataProvider
     */
    public function searchStaff($params, $class, $usernameField)
    {
        // Get the full table name (may include schema)
        $tableName = $class::tableName();

        // Join with staff using custom ON condition to support OR logic
        // Status filter must be in the ON clause to preserve LEFT JOIN behavior
        $query = $class::find()
            ->select([$tableName . '.*', 'ad_counseling.view_staff_biodata.sm_staff_name AS sm_staff_name'])
            ->leftJoin(
                'ad_counseling.view_staff_biodata',
                [
                    'and',
                    [
                        'or',
                        '[[ad_counseling.view_staff_biodata.sm_email_addr]] = [[' . $tableName . '.email]]',
                        new \yii\db\Expression('[[ad_counseling.view_staff_biodata.sm_email_addr]] = CONCAT([[' . $tableName . '.username]], \'@iium.edu.my\')')
                    ],
                    ['ad_counseling.view_staff_biodata.sm_staff_status' => '1']
                ]
            )
            ->where([$tableName . '.usertype' => 'STF']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false, // Disable pagination to preserve JOIN columns
        ]);

        // Add sorting for sm_staff_name
        $dataProvider->sort->attributes['sm_staff_name'] = [
            'asc' => ['ad_counseling.view_staff_biodata.sm_staff_name' => SORT_ASC],
            'desc' => ['ad_counseling.view_staff_biodata.sm_staff_name' => SORT_DESC],
        ];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // Apply filters
        $query->andFilterWhere(['ilike', $usernameField, $this->username]);

        if (!empty($this->sm_staff_name)) {
            $query->andWhere("
                to_tsvector('simple', ad_counseling.view_staff_biodata.sm_staff_name) @@
                plainto_tsquery(:sm_staff_name)
                OR ad_counseling.view_staff_biodata.sm_staff_name ILIKE :sm_staff_nameLike",
                [':sm_staff_name' => $this->sm_staff_name, ':sm_staff_nameLike' => '%' . $this->sm_staff_name . '%']
            );
        }

        return $dataProvider;
    }

      /**
     * Create data provider for Assignment model.
     * @param  array                        $params
     * @param  \yii\db\ActiveRecord         $class
     * @param  string                       $usernameField
     * @return \yii\data\ActiveDataProvider
     */
    public function searchStudent($params, $class, $usernameField)
    {
        $query = $class::find()->joinWith(['student'])->where(['usertype' => 'STD']);  

        // echo $query->createCommand()->getRawSql();
        // exit;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false, // Disable pagination to preserve JOIN columns
        ]);

        // Add sorting for name
        $dataProvider->sort->attributes['studentname'] = [
            'asc' => ['fdw_uia_prod.stud_biodata_vw.studentname' => SORT_ASC],
            'desc' => ['fdw_uia_prod.stud_biodata_vw.studentname' => SORT_DESC],
        ];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // Apply filters
        $query->andFilterWhere(['ilike', $usernameField, $this->username]);

        if (!empty($this->studentname)) {
            $query->andWhere("
                to_tsvector('simple', fdw_uia_prod.stud_biodata_vw.studentname) @@ 
                plainto_tsquery(:studentname) 
                OR fdw_uia_prod.stud_biodata_vw.name_name ILIKE :nameLike",
                [':studentname' => $this->studentname, ':nameLike' => '%' . $this->studentname . '%']
            );
        }

        return $dataProvider;
    }
}
