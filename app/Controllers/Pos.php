<?php

namespace App\Controllers;

use App\Controllers\BaseController;
// use App\Models\TokoModel;
use App\Models\Pos\TokoModel;

class Pos extends BaseController
{
	private $pencairanModel;

	public function __construct()
	{
		$this->TokoModel = new TokoModel();
	}

	public function index()
	{
		return view('pos/dataPos.php');
	}

	// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// pencairan
	// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	public function pencairan()
	{
		return view('pos/pencairanToko');
	}
	public function getToko($jenis = null)
	{
		$search = $this->request->getPost('search')['value'];
		$order = !empty($this->request->getPost('order')) ? $this->request->getPost('order') : '';
		$start = $this->request->getPost('start');
		$limit = $this->request->getPost('length');

		$result = $this->TokoModel->getToko($search, $start, $limit, null, $jenis)->getResult();
		$totalCount = count($this->TokoModel->getToko($search, '', '', '', $jenis)->getResultArray());

		$no = $start + 1;
		$data = [];

		foreach ($result as $key => $value) {
			switch ($value->status) {
				case '0':
					$status = 'Non Aktif';
					$textColor = 'text-danger';
					break;
				case '1':
					$status = 'Aktif';
					$textColor = 'text-success';
					break;

				default:
					$status = 'terjadi kesalahan';
					$textColor = 'text-danger';
					break;
			}

			$data[$key] = [
				$no,
				$value->nama_usaha,
				$value->usaha,
				$value->no_telepon,
				$value->email_usaha,
				$value->nama,
				$value->province,
				$value->date_add,
				'<a href="' . base_url('pos/detailPos/' . $value->company_id) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>',

			];
			$no++;
		}

		return \json_encode([
			"draw" => $_POST['draw'],
			"recordsTotal" => $totalCount,
			"recordsFiltered" => $totalCount,
			"data" => $data,
		]);
	}
	public function detailPos($company_id)
	{
		$pos = $this->TokoModel->getToko(null, null, null, $company_id)->getRowArray();

		$data['pos'] = [
			'Company Id' => $pos['company_id'],
			'Nama Usaha' => $pos['nama_usaha'],
			'Kategori Usaha' => $pos['usaha'],
			'Alamat' => $pos['alamat'],
			'Email' => $pos['email_usaha'],
			'No. Hp' => $pos['no_telepon'],
			'Rekening' =>$pos['nama_bank'].' <br> '. $pos['no_rek'] . ' - ' . $pos['nama_pemilik_rekening'],
			'Province' => $pos['province'],
			'Lat' => $pos['koordinat_lat'],
			'Lng' => $pos ['koordinat_lng'],
			'Location' => '<div id="map" class="border-2" style="width: 100%; height: 200px;"></div>',
		];
		$data['company_id'] = $pos['company_id'];
		$data['status'] = $pos['status'];
		return view('pos/detailPos', $data);
	}

	public function detailPencairan($no_transaksi)
	{
		$pencairan = $this->TokoModel->getToko('', '', '', '1', '', $no_transaksi)->getRow();

		$data['dataPencairan'] = $pencairan;
		// hanya utk tampilan
		$data['pencairan'] = [
			'no transaksi' => $pencairan->no_transaksi,
			'nama usaha' => $pencairan->nama_depan,
			'jumlah penarikan' => 'Rp ' . number_format($pencairan->nominal, 0, ',', '.'),
			'bank tujuan' => $pencairan->nama_bank,
			'rekening tujuan' => $pencairan->no_rek_tujuan . ' atas nama ' . strtoupper($pencairan->atas_nama),
			'tanggal pengajuan pencairan' => date('d/m/Y', strtotime($pencairan->tanggal)),
			'status' => $pencairan->status == 0 ? 'Belum diverifikasi' : 'Diterima',
			'keterangan' => $pencairan->keterangan ?? '-',
		];


		return view('rider/detailPencairan', $data);
	}

	public function verifikasiPencairan()
	{
		$data = $this->request->getPost();

		$pesan = $data['status'] == 1 ? "Pengajuan pencairan saldo telah diverifikasi" : "Pengajuan pencairan saldo ditangguhkan karena " . $data['pesan'] . ", mohon lengkapi persyaratan terlebih dahulu!";
	}

	public function cekStatus()
	{
		$id_penjualan = $this->request->getVar('id_penjualan');
		$id_driver = $this->request->getVar('id_driver');
		$isRejected = $this->TokoModel->db->query("SELECT * FROM t_penjualan_driver_batal WHERE id_penjualan = '$id_penjualan' AND id_driver='$id_driver'")->getNumRows();

		if ($isRejected > 0) {
			return json_encode([
				'isRejected' => true
			]);
		}

		return json_encode([
			"isRejected" => false
		]);
	}
}
