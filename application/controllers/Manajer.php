<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Manajer extends CI_Controller {

	public function __construct() {

		parent::__construct();
        $this->cek_login_manajer();
        $this->load->model('Auth_model', 'auth');
        $this->load->model('Pekerjaan_model', 'pekerjaan');
    }

    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $data = [
            'judul' 	=> 'Home',
            'content'	=> 'manajer/dashboard',
            'pekerjaan' => $this->pekerjaan->getPekerjaan(),
            'pekerjaan_total'       => $this->pekerjaan->getCountPekerjaan(),
            'pekerjaan_selesai'     => $this->pekerjaan->getCountPekerjaan('Selesai'),
            'pekerjaan_progress'    => $this->pekerjaan->getCountPekerjaan('Progress'),
            'pekerjaan_reject'      => $this->pekerjaan->getCountPekerjaan('Reject'),
            'sidebar_collapse'     => true,
        ];
        
        $this->load->view('manajer/template', $data);
    }

    public function akun()
    {
        $id = $this->session->userdata('id');

        $account = $this->auth->getAccount($id);

        $data = [
            'judul' 	=> 'Akun',
            'content'	=> 'manajer/akun',
            'akun'      => $account
        ];
        
        $this->load->view('manajer/template', $data);

    }

    public function update_akun()
    {
        $id = $this->session->userdata('id');
        $this->form_validation->set_rules('username', 'Username', 'required|alpha_numeric|min_length[5]|max_length[35]');
        $this->form_validation->set_rules('fullname', 'Fullname', 'required|alpha_numeric_spaces|min_length[5]|max_length[35]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|min_length[5]|max_length[50]');
        $this->form_validation->set_rules('password', 'password', 'required|min_length[5]|max_length[50]');

        $username        = htmlspecialchars(strip_tags(xss($this->input->post('username'))));
        $fullname        = htmlspecialchars(strip_tags(xss($this->input->post('fullname'))));
        $email           = htmlspecialchars(strip_tags(xssForMail($this->input->post('email'))));
        $password        = htmlspecialchars(strip_tags(xss($this->input->post('password'))));

        if($this->form_validation->run() == FALSE){

            $errors = $this->form_validation->error_array();
            $this->session->set_flashdata('errors', $errors);
            $this->session->set_flashdata('inputs', $this->input->post());
			redirect(base_url('manajer/akun'));

        } else {

            $data = [
                'username'      => $username,
                'fullname'      => $fullname,
                'email'         => $email,
                'password'      => password_hash($password, PASSWORD_DEFAULT),
                'pass_show'     => $password
            ];
            $this->session->set_userdata('name', $fullname);
            $ubah   = $this->auth->update($data, $id);

            if($ubah == true){
                $this->session->set_flashdata('info', 'Berhasil Mengubah Akun');
                redirect(base_url('manajer/akun'));
            } else {
                $this->session->set_flashdata('error', 'Gagal Mengubah Akun');
                redirect(base_url('manajer/akun'));
            }

        }
    }

    public function pekerjaan()
    {
        $data = [
            'judul' 	        => 'Data Pekerjaan',
            'content'	        => 'manajer/pekerjaan/index',
            'pekerjaan'         => $this->pekerjaan->getPekerjaan(),
            'plugin_datatable'  => true,
            'plugin_edit_with_modal'     => true,
            'sidebar_collapse'     => true,
        ];
        
        $this->load->view('manajer/template', $data);
    }

    public function status_pekerjaan($id = null){
        $pekerjaan = $this->pekerjaan->getPekerjaan($id);

        if(!empty($pekerjaan)){

            $status = $this->input->post('status_pekerjaan', true);

            if($status == "Selesai" && $pekerjaan['pekerjaan_progress'] == 100){

                $data = [
                    'pekerjaan_status' => 'Selesai'
                ];

            } else if($status == "Selesai" && $pekerjaan['pekerjaan_progress'] < 100){

                $this->session->set_flashdata('error', 'Anda tidak dapat memberikan status pekerjaan selesai jika progress belum 100%');
                redirect(base_url('manajer/pekerjaan'));
            
            } else {

                $data = [
                    'pekerjaan_status' => $status
                ];

            }

            $ubah = $this->pekerjaan->update($data, $id);

            if($ubah == true){
                $this->session->set_flashdata('info', 'Berhasil Mengubah Status Pekerjaan');
                redirect(base_url('manajer/pekerjaan'));
            } else {
                $this->session->set_flashdata('error', 'Gagal Mengubah Status Pekerjaan');
                redirect(base_url('manajer/pekerjaan'));
            }
        }
    }

    public function detail_pekerjaan($id = null)
    {
        $pekerjaan = $this->pekerjaan->getPekerjaan($id);
        $uploads = $this->pekerjaan->getUploads($id);

        if(!empty($pekerjaan)){

            $data = [
                'judul' 	=> 'Detail Pekerjaan',
                'content'	=> 'manajer/pekerjaan/detail',
                'pekerjaan' => $pekerjaan,
                'uploads'   => $uploads
            ];
            
            $this->load->view('manajer/template', $data);
        } else {
            echo "Tidak ada data";
        }
    }

    public function hapus_pekerjaan($id = null)
    {
        $pekerjaan = $this->pekerjaan->getPekerjaan($id);

        if(!empty($pekerjaan)){

            $hapus = $this->pekerjaan->delete($id);

            if($hapus == true){
                $this->session->set_flashdata('warning', 'Berhasil Menghapus Akun');
                redirect(base_url('manajer/pekerjaan'));
            } else {
                $this->session->set_flashdata('error', 'Gagal Menghapus Akun');
                redirect(base_url('manajer/pekerjaan'));
            }
        
        }
    }

    public function print_pekerjaan_with_pdf($id = null)
    {
        $tanggal = date('d-m-Y');
 
        $pdf = new \TCPDF();
        $pdf->AddPage('L', 'A3');
        $pdf->SetFont('', 'B', 20);
        $pdf->Cell(113, 0, "Laporan Pekerjaan - ".$tanggal, 0, 1, 'L');
        $pdf->SetAutoPageBreak(true, 0);
 
        // Add Header
        $pdf->Ln(10);
        $pdf->SetFont('', 'B', 12);
        $pdf->Cell(10, 8, "No", 1, 0, 'C');
        $pdf->Cell(55, 8, "Nama Pekerjaan", 1, 0, 'C');
        $pdf->Cell(35, 8, "Jumlah Unit", 1, 0, 'C');
        $pdf->Cell(55, 8, "Nama Kontraktor", 1, 0, 'C');
        $pdf->Cell(35, 8, "Jumlah Pekerja", 1, 0, 'C');
        $pdf->Cell(35, 8, "Tanggal Mulai", 1, 0, 'C');
        $pdf->Cell(35, 8, "Deadline", 1, 0, 'C');
        $pdf->Cell(135, 8, "Keterangan", 1, 1, 'C');
        $pdf->SetFont('', '', 12);
        if($id == null){
            $pekerjaan = $this->pekerjaan->getPekerjaan();
            foreach($pekerjaan as $k => $item) {
                $this->addRow($pdf, $k+1, $item);
                $pdf->Ln();
            }
        } else {
            $item = $this->pekerjaan->getPekerjaan($id);
            $this->addRow($pdf, 1, $item);
            $pdf->Ln();
        }
        $pdf->Output('Laporan Pekerjaan - '.$tanggal.'.pdf'); 
    }
 
    private function addRow($pdf, $no, $item) {

        if($item['pekerjaan_nama'] == 1){
            $tipe = "Kormersil (Type 32) Rumah";
            $keterangan = " unit";
        } else if($item['pekerjaan_nama'] == 2){
            $tipe = "Subsidi (Type 25) Rumah";
            $keterangan = " unit";
        } else {
            $tipe = "Sarana dan Prasarana";
            $keterangan = " /m2";
        }
        $pdf->Cell(10, 8, $no, 1, 0, 'C');
        $pdf->Cell(55, 8, $tipe, 1, 0, '');
        $pdf->Cell(35, 8, $item['pekerjaan_unit']." ".$keterangan, 1, 0, '');
        $pdf->Cell(55, 8, $item['pekerjaan_kontraktor'], 1, 0, '');
        $pdf->Cell(35, 8, $item['pekerjaan_jumlah_pekerja'], 1, 0, '');
        $pdf->Cell(35, 8, date('d-m-Y', strtotime($item['pekerjaan_tgl_mulai'])), 1, 0, 'C');
        $pdf->Cell(35, 8, date('d-m-Y', strtotime($item['pekerjaan_deadline'])), 1, 0, 'C');
        $pdf->Cell(135, 8, $item['pekerjaan_keterangan'], 1, 0, 'C');
    }

    public function print_pekerjaan_with_excel($id = null)
    {
        $tanggal = date('d-m-Y');
        // panggil class Sreadsheet baru
        $spreadsheet = new Spreadsheet;
        // Buat custom header pada file excel
        $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'No')
                    ->setCellValue('B1', 'Nama Pekerjaan')
                    ->setCellValue('C1', 'Jumlah Unit')
                    ->setCellValue('D1', 'Nama Kontraktor')
                    ->setCellValue('E1', 'Jumlah Pekerja')
                    ->setCellValue('F1', 'Tanggal Mulai')
                    ->setCellValue('G1', 'Deadline')
                    ->setCellValue('H1', 'Keterangan');
        // define kolom dan nomor
        $kolom = 2;
        $nomor = 1;
        // tambahkan data pekerjaan ke dalam file excel
        if($id == null){
            $pekerjaan = $this->pekerjaan->getPekerjaan();
            foreach($pekerjaan as $data) {
                if($data['pekerjaan_nama'] == 1){
                    $tipe = "Kormersil (Type 32) Rumah";
                    $keterangan = " unit";
                } else if($data['pekerjaan_nama'] == 2){
                    $tipe = "Subsidi (Type 25) Rumah";
                    $keterangan = " unit";
                } else {
                    $tipe = "Sarana dan Prasarana";
                    $keterangan = " /m<sup>2</sup>";
                }
                $spreadsheet->setActiveSheetIndex(0)
                            ->setCellValue('A' . $kolom, $nomor)
                            ->setCellValue('B' . $kolom, $tipe)
                            ->setCellValue('C' . $kolom, $data['pekerjaan_unit']." ".$keterangan)
                            ->setCellValue('D' . $kolom, $data['pekerjaan_kontraktor'])
                            ->setCellValue('E' . $kolom, $data['pekerjaan_jumlah_pekerja'])
                            ->setCellValue('F' . $kolom, date('j F Y', strtotime($data['pekerjaan_tgl_mulai'])))
                            ->setCellValue('G' . $kolom, date('j F Y', strtotime($data['pekerjaan_deadline'])))
                            ->setCellValue('H' . $kolom, $data['pekerjaan_keterangan']);
                $kolom++;
                $nomor++;
            }
        } else {
            $pekerjaan = $this->pekerjaan->getPekerjaan($id);
            if(!empty($pekerjaan)){
                if($pekerjaan['pekerjaan_nama'] == 1){
                    $tipe = "Kormersil (Type 32) Rumah";
                    $keterangan = " unit";
                } else if($pekerjaan['pekerjaan_nama'] == 2){
                    $tipe = "Subsidi (Type 25) Rumah";
                } else {
                    $tipe = "Sarana dan Prasarana";
                    $keterangan = " /m<sup>2</sup>";
                }
                $spreadsheet->setActiveSheetIndex(0)
                                ->setCellValue('A' . $kolom, $nomor)
                                ->setCellValue('B' . $kolom, $tipe)
                                ->setCellValue('C' . $kolom, $pekerjaan['pekerjaan_unit']." ".$keterangan)
                                ->setCellValue('D' . $kolom, $pekerjaan['pekerjaan_kontraktor'])
                                ->setCellValue('E' . $kolom, $pekerjaan['pekerjaan_jumlah_pekerja'])
                                ->setCellValue('F' . $kolom, date('j F Y', strtotime($pekerjaan['pekerjaan_tgl_mulai'])))
                                ->setCellValue('G' . $kolom, date('j F Y', strtotime($pekerjaan['pekerjaan_deadline'])))
                                ->setCellValue('H' . $kolom, $pekerjaan['pekerjaan_keterangan']);
            } else {
                $this->session->set_flashdata('error', 'Gagal Membuat Laporan Pekerjaan');
                redirect(base_url('pengawas/pekerjaan'));
            }
        }
        // download spreadsheet dalam bentuk excel .xlsx
        $writer = new Xlsx($spreadsheet);
    
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Laporan_Pekerjaan_"'.str_replace(" ", "_". $tanggal).'".xlsx"');
        header('Cache-Control: max-age=0');
    
        $writer->save('php://output');
    }

}