<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Alarm;

/**
 * AlarmSearch represents the model behind the search form about `common\models\Alarm`.
 */
class AlarmSearch extends Alarm
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'alert_id', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['details', 'type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     * Id $userId give show only for that user
     * 
     * 
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $userId = null)
    {
        $query = Alarm::find()->orderBy(['id' => SORT_DESC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'alert_id' => $this->alert_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ]);
        
        if($userId)
            $query->andWhere(['user_id' => $userId]);

        $query->andFilterWhere(['like', 'details', $this->details])
            ->andFilterWhere(['like', 'type', $this->type]);

        return $dataProvider;
    }
}
