<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Matakuliah;

/**
 * MatakuliahSearch represents the model behind the search form of `app\models\Matakuliah`.
 */
class MatakuliahSearch extends Matakuliah
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'teori', 'jml_kelas', 'praktek', 'id_kurikulum', 'id_jurusan'], 'integer'],
            [['nama'], 'safe'],
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
        $query = Matakuliah::find();

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
            'id_kurikulum' => $this->id_kurikulum,
            'id_jurusan' => $this->id_jurusan,
        ]);

        $query->andFilterWhere(['like', 'nama', $this->nama])
            ->andFilterWhere(['like', 'teori', $this->teori])
            ->andFilterWhere(['like', 'praktek', $this->praktek])
            ->andFilterWhere(['like', 'jml_kelas', $this->jml_kelas]);
            // ->andFilterWhere(['like', 'kurikulum', $this->kurikulum])
            // ->andFilterWhere(['like', 'jml_kelas', $this->jml_kelas]);
            // ->andFilterWhere(['like', 'wp', $this->wp]);

        return $dataProvider;
    }
}
