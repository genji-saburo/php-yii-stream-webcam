<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Camera;

/**
 * CameraSearch represents the model behind the search form about `common\models\Camera`.
 */
class CameraSearch extends Camera {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'address', 'port', 'login', 'password', 'serial_number', 'property_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params) {
        $query = Camera::find();

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'address', $this->address])
                ->andFilterWhere(['like', 'port', $this->port])
                ->andFilterWhere(['like', 'login', $this->login])
                ->andFilterWhere(['like', 'password', $this->password])
                ->andFilterWhere(['like', 'property_id', $this->property_id])
                ->andFilterWhere(['like', 'serial_number', $this->serial_number]);

        $query->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }

}
