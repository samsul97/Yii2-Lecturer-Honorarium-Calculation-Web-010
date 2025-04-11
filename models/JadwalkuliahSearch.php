<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Jadwalkuliah;

/**
 * JadwalkuliahSearch represents the model behind the search form of `app\models\Jadwalkuliah`.
 */
class JadwalkuliahSearch extends Jadwalkuliah
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_mk', 'id_dosen', 'id_kelas', 'id_kategori', 'id_semester', 'id_ruang'], 'integer'],
            [['thn_akademik', 'hari', 'jam_awal', 'jam_akhir'], 'safe'],
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
        $query = Jadwalkuliah::find();

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
            'id_mk' => $this->id_mk,
            'id_kelas' => $this->id_kelas,
            'id_dosen' => $this->id_dosen,
            'id_semester' => $this->id_semester,
            'id_ruang' => $this->id_ruang,
            // 'id_hari' => $this->id_hari,
        ]);

        $query->andFilterWhere(['like', 'jam_awal', $this->jam_awal])
            ->andFilterWhere(['like', 'jam_akhir', $this->jam_akhir])
            ->andFilterWhere(['like', 'hari', $this->hari]);
        return $dataProvider;
    }
}
