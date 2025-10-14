<?php

namespace mdm\admin\models\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * User represents the model behind the search form about `mdm\admin\models\User`.
 */
class User extends Model
{
    public $id;
    public $username;
    public $email;
    public $status;
    public $sm_staff_name;
    public $studentname;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status',], 'integer'],
            [['username', 'email', 'sm_staff_name', 'studentname'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $class = Yii::$app->getUser()->identityClass ? : 'mdm\admin\models\User';
        $query = $class::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance for staff users with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function searchStaff($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $class = Yii::$app->getUser()->identityClass ? : 'mdm\admin\models\User';

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
        ]);

        // Add sorting for sm_staff_name
        $dataProvider->sort->attributes['sm_staff_name'] = [
            'asc' => ['ad_counseling.view_staff_biodata.sm_staff_name' => SORT_ASC],
            'desc' => ['ad_counseling.view_staff_biodata.sm_staff_name' => SORT_DESC],
        ];

        $this->load($params);
        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $query->andFilterWhere([
            $tableName . '.id' => $this->id,
            $tableName . '.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', $tableName . '.username', $this->username])
            ->andFilterWhere(['like', $tableName . '.email', $this->email]);

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
     * Creates data provider instance for student users with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function searchStudent($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $class = Yii::$app->getUser()->identityClass ? : 'mdm\admin\models\User';
        $query = $class::find()->joinWith(['student'])->where(['usertype' => 'STD']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Add sorting for studentname
        $dataProvider->sort->attributes['studentname'] = [
            'asc' => ['fdw_uia_prod.stud_biodata_vw.studentname' => SORT_ASC],
            'desc' => ['fdw_uia_prod.stud_biodata_vw.studentname' => SORT_DESC],
        ];

        $this->load($params);
        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email]);

        if (!empty($this->studentname)) {
            $query->andWhere("
                to_tsvector('simple', fdw_uia_prod.stud_biodata_vw.studentname) @@
                plainto_tsquery(:studentname)
                OR fdw_uia_prod.stud_biodata_vw.studentname ILIKE :studentnameLike",
                [':studentname' => $this->studentname, ':studentnameLike' => '%' . $this->studentname . '%']
            );
        }

        return $dataProvider;
    }
}
