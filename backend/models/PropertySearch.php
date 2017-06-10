<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Property;

/**
 * PropertySearch represents the model behind the search form about `common\models\Property`.
 */
class PropertySearch extends Property {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'coord_lng', 'created_at', 'updated_at'], 'integer'],
            [['name', 'pin_code', 'phone1', 'phone2', 'phone3', 'coord_lat', 'owner_name', 'address', 'phone_police', 'security_status'], 'safe'],
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
        $query = Property::find();

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
            'coord_lng' => $this->coord_lng,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'updated_at' => $this->security_status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'pin_code', $this->pin_code])
                ->andFilterWhere(['like', 'phone1', $this->phone1])
                ->andFilterWhere(['like', 'phone2', $this->phone2])
                ->andFilterWhere(['like', 'phone3', $this->phone3])
                ->andFilterWhere(['like', 'coord_lat', $this->coord_lat]);

        $query->orderBy(['id' => SORT_DESC]);
        
        return $dataProvider;
    }

}
