<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Kehadiran;

/**
 * KehadiranSearch represents the model behind the search form of `app\models\Kehadiran`.
 */
class KehadiranSearch extends Kehadiran
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_kelas', 'id_dosen', 'id_mk'], 'integer'],
            [['tgl', 'keterangan', 'status'], 'safe'],
            // [['status'], 'enum'],
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
        $query = Kehadiran::find();

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
            'tgl' => $this->tgl,
            'id_kelas' => $this->id_kelas,
            'id_dosen' => $this->id_dosen,
            'id_mk' => $this->id_mk,
        ]);

        $query->andFilterWhere(['like', 'status', $this->status])
                ->andFilterWhere(['like', 'keterangan', $this->keterangan]);
                // ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
