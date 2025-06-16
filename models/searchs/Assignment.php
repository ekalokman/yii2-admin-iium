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
    public $name; 

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'username', 'email', 'sm_staff_name', 'name'], 'safe'],
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
        $query = $class::find()->joinWith(['staff'])->where(['usertype' => 'STF']);  

        // echo $query->createCommand()->getRawSql();
        // exit;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Add sorting for sm_staff_name
        $dataProvider->sort->attributes['sm_staff_name'] = [
            'asc' => ['fdw_huris.view_staff_biodata.sm_staff_name' => SORT_ASC],
            'desc' => ['fdw_huris.view_staff_biodata.sm_staff_name' => SORT_DESC],
        ];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // Apply filters
        $query->andFilterWhere(['ilike', $usernameField, $this->username]);

        if (!empty($this->sm_staff_name)) {
            $query->andWhere("
                to_tsvector('simple', fdw_huris.view_staff_biodata.sm_staff_name) @@ 
                plainto_tsquery(:sm_staff_name) 
                OR fdw_huris.view_staff_biodata.sm_staff_name ILIKE :sm_staff_nameLike",
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
        ]);

        // Add sorting for name
        $dataProvider->sort->attributes['name'] = [
            'asc' => ['fdw_ac.stud_biodata_vw.studentname' => SORT_ASC],
            'desc' => ['fdw_ac.stud_biodata_vw.studentname' => SORT_DESC],
        ];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // Apply filters
        $query->andFilterWhere(['ilike', $usernameField, $this->username]);

        if (!empty($this->name)) {
            $query->andWhere("
                to_tsvector('simple', fdw_ac.stud_biodata_vw.studentname) @@ 
                plainto_tsquery(:name) 
                OR fdw_ac.stud_biodata_vw.name_name ILIKE :nameLike",
                [':name' => $this->name, ':nameLike' => '%' . $this->name . '%']
            );
        }

        return $dataProvider;
    }
}
