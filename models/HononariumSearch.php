<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Hononarium;

/**
 * HononariumSearch represents the model behind the search form of `app\models\Hononarium`.
 */
class HononariumSearch extends Hononarium
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_hadir', 'id_dosen', 'id_mk'], 'integer'],
            [['periode', 'jum_sks', 'jum_hadir', 'jum_honor'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
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
    public function search($params)
    {
        $query = Hononarium::find();

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
            'id_hadir' => $this->id_hadir,
            'id_dosen' => $this->id_dosen,
            'id_mk' => $this->id_mk,
        ]);

        $query->andFilterWhere(['like', 'periode', $this->periode])
            ->andFilterWhere(['like', 'jum_sks', $this->jum_sks])
            ->andFilterWhere(['like', 'jum_hadir', $this->jum_hadir])
            ->andFilterWhere(['like', 'jum_honor', $this->jum_honor]);

        return $dataProvider;
    }
}
