        <?php foreach (Dosen::findAllJabatan() as $dosen): ?>
          <?php if ($dosen->id_jabatan): ?>
            <tr>
              <th colspan="3" class="info">Jabatan <?= @$dosen->jabatan->nama; ?></th>
            </tr>
            <!-- <?php $jabatan = $dosen->id_jabatan ?> -->
            <!-- <?php $i = 1; ?> -->
          <?php else: ?>
          <tr>
            <td style="text-align: center;"><?= $i++; ?></td>
            <td><?= Html::a(@$dosen->nama); ?></td>
            <!-- <td style="text-align: center;"><?= $dosen->manyJabatanCount; ?></td> -->
          </tr>
        <?php endif; ?>
        <?php endforeach ?><?php

          $jabatan = MJabatan::find()->all();

          foreach ($jabatan as $jab) {
            
            echo '<tr><th colspan="3" class="info">Jabatan ' . 

            $jab->nama . 

            "</th></tr>";

            $i = 1;

            foreach (Dosen::findAllJabatan() as $dosen) {

              if ($jab->id === $dosen->id_jabatan) {
                echo '<tr>
                    <td style="text-align: center;">' . $i++ . '</td>
                    <td>' . $dosen->nama . '</td>
                    <!-- <td style="text-align: center;">' . $dosen->manyJabatanCount . '</td> -->
                  </tr>';
                }
            }

          }

        ?>