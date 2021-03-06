<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Data Pekerjaan</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="<?= base_url('admin/pekerjaan') ?>">Pekerjaan</a></li>
              <li class="breadcrumb-item active">Here</li>
            </ol>
          </div>
        </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              Data Pekerjaan
              <div class="btn-group float-right">
                <a href="<?= base_url('admin/print-pekerjaan-with-pdf') ?>" class="btn btn-sm btn-danger float-right"><i class="fa fa-print"></i> Print Pekerjaan (PDF)</a>
                <a href="<?= base_url('admin/print-pekerjaan-with-excel') ?>" class="btn btn-sm btn-success float-right"><i class="fa fa-print"></i> Print Pekerjaan (Excel)</a>
              </div>
            </div>
            <div class="card-body">
                <?php if(!empty($this->session->flashdata('success'))) { ?>
                <div class="alert alert-success">
                    <?= $this->session->flashdata('success') ?>
                </div>
                <?php } else if(!empty($this->session->flashdata('info'))) { ?>
                <div class="alert alert-info">
                    <?= $this->session->flashdata('info') ?>
                </div>
                <?php } else if(!empty($this->session->flashdata('warning'))) { ?>
                <div class="alert alert-warning">
                    <?= $this->session->flashdata('warning') ?>
                </div>
                <?php } else if(!empty($this->session->flashdata('error'))) { ?>
                <div class="alert alert-danger">
                    <?= $this->session->flashdata('error') ?>
                </div>
                <?php } else {} ?>
                <div class="table-responsive">
                    <table id="table" class="table table-sm table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pekerjaan</th>
                            <th>Jumlah</th>
                            <th>Nama Kontraktor</th>
                            <th>Jumlah Pekerja</th>
                            <th>Tanggal Mulai</th>
                            <th>Deadline</th>
                            <th>Keterangan</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($pekerjaan as $key => $item) { ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td>
                              <?php 
                                if($item['pekerjaan_nama'] == 1){
                                  echo "Kormersil (Type 32) Rumah";
                                  $keterangan = " unit";
                                } else if($item['pekerjaan_nama'] == 2){
                                  echo "Subsidi (Type 25) Rumah";
                                  $keterangan = " unit";
                                } else {
                                  echo "Sarana dan Prasarana";
                                  $keterangan = " /m<sup>2</sup>";
                                }
                              ?>
                            </td>
                            <td><?= $item['pekerjaan_unit'].$keterangan ?></td>
                            <td><?= $item['pekerjaan_kontraktor'] ?></td>
                            <td><?= $item['pekerjaan_jumlah_pekerja'] ?></td>
                            <td><?= date('d-m-Y', strtotime($item['pekerjaan_tgl_mulai'])) ?></td>
                            <td><?= date('d-m-Y', strtotime($item['pekerjaan_deadline'])) ?></td>
                            <td><?= $item['pekerjaan_keterangan'] ?></td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

</div>

<div class="modal" id="ubahStatusPekerjaan">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Ubah Status Pekerjaan</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <?= form_open('#', ['id' => 'formUbahStatusPekerjaan']) ?>                      
      <!-- Modal body -->
      <div class="modal-body">
        <label for="">Status Pekerjaan</label>
        <?= form_dropdown('status_pekerjaan', ['' => 'Pilih Status', 'Pekerjaan Baru' => 'Pekerjaan Baru', 'Progress' => 'Approve', 'Selesai' => 'Selesai', 'Reject' => 'Batalkan'], '', ['class' => 'form-control', 'id' => 'status_pekerjaan']) ?>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-info">Update</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>