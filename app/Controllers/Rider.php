<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PencairanModel;
use App\Models\Rider\RiderModel;
use App\Models\Rider\TopUpModel;

class Rider extends BaseController
{
	private $riderModel;
	private $topUpModel;
	private $pencairanModel;

	public function __construct()
	{
		$this->riderModel = new RiderModel();
		$this->topUpModel = new TopUpModel();
		$this->pencairanModel = new PencairanModel();
	}

	// ========================================================================================================================================
	// Fitur verifikasi pendaftaran Rider
	// ========================================================================================================================================

	public function index()
	{
		return view('rider/dataRider');
	}

	public function kendaraan()
	{
		return view('rider/kendaraan');
	}

	public function getRider($jenis = null)
	{
		$search = $this->request->getPost('search')['value'];
		$order = !empty($this->request->getPost('order')) ? $this->request->getPost('order') : '';
		$start = $this->request->getPost('start');
		$limit = $this->request->getPost('length');

		$result = $this->riderModel->getRider($search, $start, $limit, null, $jenis)->getResult();
		$totalCount = count($this->riderModel->getRider($search, '', '', '', $jenis)->getResultArray());

		$no = $start + 1;
		$data = [];

		foreach ($result as $key => $value) {
			switch ($value->status) {
				case '-1':
					$status = 'Pre Registrasi';
					$text = 'text-dark';
					break;
				case '0':
					$status = 'Telah Registrasi';
					$text = 'text-danger';
					break;
				case '1':
					$status = 'Pengajuan';
					$text = 'text-indigo';
					break;
				case '2':
					$status = 'Pengembalian';
					$text = 'text-warning';
					break;
				case '3':
					$status = 'Aktif';
					$text = 'text-success';
					break;

				default:
					$status = 'Nonaktif';
					$text = 'text-gray';
					break;
			}

			$data[$key] = [
				$no,
				$value->no_ktp,
				$value->nama_depan,
				$value->alamat_tinggal,
				$value->hp1 . "<br>" . $value->hp2,
				$value->email,
				"Rp " . number_format($value->saldo, 0, "", "."),
				'<span class="' . $text . ' font-weight-bold">' . $status . '</span>',
				'<a href="' . base_url('rider/detail/' . $value->kd_driver) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>',
				$jenis == 'baru' ? '<button class="btn btn-primary btn-sm verifikasi" data-driver="' . $value->kd_driver . '"><i class="fas fa-check mr-1"></i> Verifikasi</button>' : '',
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

	public function detail($kd_driver)
	{
		$rider = $this->riderModel->getRider(null, null, null, $kd_driver)->getRowArray();
		$sim = $this->riderModel->getsim($kd_driver);
		$tahun_lahir = substr($rider['no_ktp'], 10, 2);

		// dokumen data diri
		$foto_ktp = file_exists(FCPATH . '/../kajek/images/ktp/' . $rider['kd_driver'] . 'ktp.jpg') ? base_url() . '/../kajek/images/ktp/' . $rider['kd_driver'] . 'ktp.jpg' : base_url() . '/assets/file-not-found.png';
		$foto_skck = file_exists(FCPATH . '/../kajek/images/skck/' . $rider['kd_driver'] . 'skck.jpg') ? base_url() . '/../kajek/images/skck/' . $rider['kd_driver'] . 'skck.jpg' : base_url() . '/assets/file-not-found.png';

		//dokumen kendaraan
		$foto_stnk = file_exists(FCPATH . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'stnk.jpg') ? base_url() . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'stnk.jpg' : base_url() . '/assets/file-not-found.png';
		$foto_pajak = file_exists(FCPATH . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'pajak.jpg') ? base_url() . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'pajak.jpg' : base_url() . '/assets/file-not-found.png';
		$foto_depan = file_exists(FCPATH . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'depan.jpg') ? base_url() . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'depan.jpg' : base_url() . '/assets/file-not-found.png';
		$foto_kanan = file_exists(FCPATH . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'kanan.jpg') ? base_url() . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'kanan.jpg' : base_url() . '/assets/file-not-found.png';
		$foto_belakang = file_exists(FCPATH . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'belakang.jpg') ? base_url() . '/../kajek/images/kendaraan/' . $rider['kd_kendaraan'] . $rider['kd_driver'] . 'belakang.jpg' : base_url() . '/assets/file-not-found.png';
	foreach ($sim as $key => $value) {
		$foto_sim = file_exists(FCPATH . '/../kajek/images/sim/' . $value->sim_path . '') ? base_url() . '/../kajek/images/sim/' .$value->sim_path . '' : base_url() . '/assets/file-not-found.png';
		$data_sim[] = '<img class="img-thumbnail btn-dok" src="'.$foto_sim.'" data-title="Foto SIM" />';
	}
	$tanggal_lahir=(intval(substr($rider["no_ktp"], 6, 2)) >40) ? intval(substr($rider["no_ktp"], 6, 2)) - 40 : substr($rider["no_ktp"], 6, 2);
		$data['rider'] = [
			'No. Ktp' => $rider['no_ktp'],
			'Nama Rider' => $rider['nama_depan'],
			'Tgl. Lahir' => substr($rider["no_ktp"], 6, 2) . '-' . substr($rider["no_ktp"], 8, 2) . '-' . (date('y') - intval($tahun_lahir) <= 0 ? '19' . $tahun_lahir : '20' . $tahun_lahir),
			'Jenis Kelamin' => intval(substr($rider['no_ktp'], 6, 2)) > 40 ? 'Perempuan' : 'Laki-laki',
			'Alamat' => $rider['alamat_tinggal'],
			'No. Hp' => $rider['hp1'] . ' - ' . $rider['hp2'],
			'Email' => $rider['email'],
			'Zona' => $rider['deskripsi'],
			'Merk Kendaraan' => $rider['merk_nama'],
			'Model' => $rider['model_nama'],
			'Plat Nomor' => $rider['nomor_plat'],
			'Tahun Pembuatan' => $rider['tahun_pembuatan'],
			'Tgl Kadaluarsa STNK' => date('d/m/Y', strtotime($rider['STNK_expired'])),
			'Dokumen Data Diri' =>
			'
					<img class="img-thumbnail btn-dok" src="' . $foto_ktp . '" data-title="Foto KTP" />
					'.implode($data_sim).'
					<img class="img-thumbnail btn-dok" src="' . $foto_skck . '" data-title="Foto SKCK" />
			',
			'Dokumen Kendaraan' =>
			'
					<img class="img-thumbnail btn-dok" src="' . $foto_stnk . '" data-title="Foto STNK" />
					<img class="img-thumbnail btn-dok" src="' . $foto_pajak . '" data-title="Foto pajak" />
					<img class="img-thumbnail btn-dok" src="' . $foto_depan . '" data-title="Foto tampak depan" />
					<img class="img-thumbnail btn-dok" src="' . $foto_kanan . '" data-title="Foto tampak kanan" />
					<img class="img-thumbnail btn-dok" src="' . $foto_belakang . '" data-title="Foto tampak belakang" />
			',
		];
		$data['kd_driver'] = $rider['kd_driver'];
		$data['status'] = $rider['status'];
		return view('rider/detailRider', $data);
	}

	public function verifikasi()
	{
		$kd_driver = $this->request->getPost('kd_driver'); // ambil kode driver dari request 
		$setLocation = $this->riderModel->db->query("UPDATE m_driver_location_log SET driver_state=1 WHERE kd_driver='$kd_driver'"); // update driver location log
		$setStatus = $this->riderModel->update($kd_driver, ['status' => '3']); // update status rider menjadi 3 (aktif)
		if ($setStatus && $setLocation) {
			$this->session->setFlashdata('sukses', 'Rider dengan id ' . $kd_driver . ' telah diverifikasi'); // tampilkan toast ke aplikasi
			$insert = $this->riderModel->db->query("INSERT INTO m_driver_kendaraan_log(kd_kendaraan,tanggal,aktivitas,pesan)
			SELECT kd_kendaraan,NOW(),'Verifikasi Kendaraan Rider','sukses'
			FROM m_driver_kendaraan 
			WHERE kd_driver='$kd_driver' AND status=2");
			$this->sendNotifToRider($kd_driver, 'Selamat, data anda sudah divalidasi. Silahkan Log Out dan Login kembali ke aplikasi untuk memulai aktifitas anda.'); //kirim notif ke rider
			return json_encode([
				'success' => true,
				'redirect' => base_url('rider'),
				'driver' => $kd_driver
			]);
		}

		return json_encode([
			'success' => false,
			'msg' => 'Gagal melakukan validasi'
		]);
	}

	public function perbaikan()
	{
		$kd_driver = $this->request->getPost('kd_driver');
		$pesan = $this->request->getPost('pesan'); //pesan perbaikan
		$updateStatus = $this->riderModel->update($kd_driver, ['status' => '2']);

		if ($updateStatus) {
			$this->session->setFlashdata('sukses', 'Rider dengan id ' . $kd_driver . ' telah dikirimi pengajuan perbaikan data'); // tampilkan toast ke aplikasi
			$this->sendNotifToRider(
				$kd_driver,
				$pesan,
				8
			); //kirim notif ke rider
			return json_encode([
				'success' => true,
				'redirect' => base_url('rider')
			]);
		}

		return json_encode([
			'success' => false,
			'msg' => 'Gagal melakukan pengajuan perbaikan'
		]);
	}

	// ==========================================================================================================================================
	// Fitur verifikasi top up rider
	// ==========================================================================================================================================
	public function tolak()
	{

	}
	public function topUp()
	{
		// dd($this->topUpModel->getData('', '', '', '2')->getResult());
		return view('rider/topUp');
	}

	public function getTopUp($jenis = null, $status = null)
	{
		$search = $this->request->getPost('search')['value'];
		$order = !empty($this->request->getPost('order')) ? $this->request->getPost('order') : '';
		$start = $this->request->getPost('start');
		$limit = $this->request->getPost('length');

		$result = $this->topUpModel->getData($search, $start, $limit, $jenis, $status)->getResult();
		$totalCount = count($this->topUpModel->getData($search, '', '', $jenis, $status)->getResultArray());

		$no = $start + 1;
		$data = [];

		foreach ($result as $key => $value) {
			switch ($value->status) {
				case '0':
					$status = 'Belum Verifikasi';
					$textColor = 'text-danger';
					break;
				case '1':
					$status = 'Diterima';
					$textColor = 'text-success';
					break;

				default:
					$status = 'terjadi kesalahan';
					$textColor = 'text-danger';
					break;
			}

			$data[$key] = [
				$no,
				$value->nama_depan,
				$value->no_rek_pengirim,
				'Rp ' . number_format($value->nominal, 0, ',', '.'),
				$value->nama_bank,
				date('d/m/Y H:i:s', strtotime($value->tanggal)),
				"<span class='$textColor font-weight-bold'>$status</span>",
				'<a href="' . base_url('rider/top-up/detail/' . $value->no_transaksi) . '" class="btn btn-info btn-sm"><i class="fas fa-eye mr-1"></i> Detail</a>',
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

	public function detailTopUp($no_transaksi)
	{
		$topUp = $this->topUpModel->getData('', '', '', '2', '',  $no_transaksi)->getRow();
		$data['dataTopUp'] = $topUp;
		$disable = $topUp->status == '1' ? 'disabled' : '';
		$bukti = file_exists(FCPATH . '/../kajek/images/' . $topUp->bukti_tf) ? base_url() . '/../kajek/images/' . $topUp->bukti_tf : base_url() . '/assets/file-not-found.png';
		$data['topUp'] = [
			'Kode Top Up' => $topUp->no_transaksi,
			'Nama Rider' => $topUp->nama_depan,
			'Nominal Top Up' => 'Rp ' . number_format($topUp->nominal, 0, ',', '.'),
			'Tanggal Top Up' => date('d/m/Y H:i:s', strtotime($topUp->tanggal)),
			'No. Rekening' => $topUp->no_rek_pengirim,
			'Bank' => $topUp->nama_bank,
			'Status' => ($topUp->status == '0' ? 'belum verifikasi' : 'Sudah diverifikasi'),
			// 'bukti Top Up' => '<button class="btn bg-navy btn-sm btn-dok" data-source="/kajek/images/topup/' . $topUp->bukti_tf . '" ' . $disable . '>Foto Bukti</button>',
			// 'bukti Top Up' => '<button class="btn bg-navy btn-sm btn-dok" data-source="' . $bukti . '" ' . $disable . '>Foto Bukti</button>',
			'bukti Top Up' => '<img src="' . $bukti . '" class="img-thumbnail btn-dok w-25" data-title="Foto bukti transfer" />',
		];

		return view('rider/detailTopUp', $data);
	}

	public function verifikasiTopUp()
	{
		$data = $this->request->getPost();


		$pesan = $data['status'] == 1 ? 'Top Up sudah diverifikasi, selamat bekerja!' : 'Top Up ditolak dengan pesan: ' . $data['pesan'];

		$approved = $this->topUpModel->update($data['no_transaksi'], ['approveat' => date('Y-m-d H:i:s'), 'status' => $data['status'], 'kd_admin' => '1']);
		if ($approved) {

			// jika top up terverifikasi
			if ($data['status'] == 1) {
				unset($data['status']);
				// return json_encode(['data' => $data]);
				$insertTopUp = $this->topUpModel->insertTopUp($data);

				if ($insertTopUp) {
					$updateSaldo = $this->topUpModel->db->query("UPDATE m_saldo_driver SET saldo = saldo + " . $data['nominal'] . " WHERE kd_driver = '" . $data['id'] . "'");

					if ($updateSaldo) {
						$this->session->setFlashdata('sukses', 'Top Up diverifikasi'); // tampilkan toast ke aplikasi
						$this->sendNotifToRider($data['id'], $pesan, 3); //kirim notif ke rider
						return json_encode([
							'success' => true,
							'redirect' => base_url('rider/top-up'),
						]);
					}

					// gagal update saldo
					return json_encode([
						'success' => false,
						'msg' => 'Gagal update saldo'
					]);
				}

				// gagal insert ke tabel top up
				return json_encode([
					'success' => false,
					'msg' => 'Gagal menambah data top up'
				]);
			}

			// jika topup ditolak
			$this->session->setFlashdata('sukses', 'Top Up ditolak'); // tampilkan toast ke aplikasi
			$this->sendNotifToRider($data['id'], $pesan, 2); //kirim notif ke rider
			return json_encode([
				'success' => true,
				'redirect' => base_url('rider/top-up'),
			]);
		}

		return json_encode([
			'success' => false,
			'msg' => 'Gagal melakukan verifikasi top up'
		]);
	}

	// ===========================================================================================================================================
	// fitur pencairan saldo
	// ===========================================================================================================================================

	public function pencairan()
	{
		return view('rider/pencairanRider');
	}

	public function getPencairan($jenis, $status = null)
	{
		$search = $this->request->getPost('search')['value'];
		$order = !empty($this->request->getPost('order')) ? $this->request->getPost('order') : '';
		$start = $this->request->getPost('start');
		$limit = $this->request->getPost('length');

		$result = $this->pencairanModel->getPencairan($search, $start, $limit, $jenis, $status)->getResult();
		$totalCount = count($this->pencairanModel->getPencairan($search, '', '', $jenis, $status)->getResultArray());

		$no = $start + 1;
		$data = [];

		foreach ($result as $key => $value) {
			switch ($value->status) {
				case '0':
					$status = 'Belum Verifikasi';
					$textColor = 'text-danger';
					break;
				case '1':
					$status = 'Diterima';
					$textColor = 'text-success';
					break;
				case '-1':
					$status = 'Ditilak';
					$textColor = 'text-danger';
					break;

				default:
					$status = 'terjadi kesalahan';
					$textColor = 'text-danger';
					break;
			}

			$data[$key] = [
				$no,
				$value->no_transaksi,
				$value->nama_depan,
				'Rp ' . number_format($value->saldo, 0, ',', '.'),
				'Rp ' . number_format($value->nominal, 0, ',', '.'),
				$value->nama_bank,
				$value->no_rek_tujuan . '<br> A.N ' . $value->atas_nama,
				"<span class='$textColor font-weight-bold'>$status</span>",
				'<a href="' . base_url('rider/pencairan/detail/' . $value->no_transaksi) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>',
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

	public function detailPencairan($no_transaksi)
	{
		$pencairan = $this->pencairanModel->getPencairan('', '', '', '2', '', $no_transaksi)->getRow();

		$data['no_transaksi'] = $pencairan->no_transaksi;
		$data['kd_driver'] = $pencairan->id;
		$data['nominal'] = $pencairan->nominal;
		$data['approveAt'] = $pencairan->approveat;
		$data['status'] = $pencairan->status;

		// hanya utk tampilan
		$data['pencairan'] = [
			'no transaksi' => $pencairan->no_transaksi,
			'nama rider' => $pencairan->nama_depan,
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
		$no_transaksi = $this->request->getPost('no_transaksi');
		$kd_driver = $this->request->getPost('kd_driver');
		$nominal = $this->request->getVar('nominal');
		$status = $this->request->getPost('status');
		

		$this->pencairanModel->transStart();
		$pesan = $status == 1 ? 'Pengajuan pencairan saldo sudah diverifikasi' : 'Pengajuan pencairan saldo ditangguhkan karena ' . $this->request->getPost('pesan') . ', proses pencairan akan diproses apabila permohonan valid';
		$approved = $this->pencairanModel->update($no_transaksi, ['approveat' => date('Y-m-d H:i:s'), 'status' => $status,]);
		if ($status == 1) {
			$pencairanValidasi = $this->pencairanModel->find($no_transaksi);
		$updatestatus = $this->pencairanModel->db->query("UPDATE m_saldo_driver SET saldo = saldo - $nominal WHERE kd_driver = '$kd_driver'");
		$insertPencairan = $this->pencairanModel->insertPenarikan([
			'no_transaksi' => $pencairanValidasi["no_transaksi"],
			'tanggal' => date('Y-m-d H:i:s'),
			'id' => $pencairanValidasi["id"],
			'jenis_user' => $pencairanValidasi["jenis_user"],
			'nominal' => $pencairanValidasi["nominal"],
			'kd_bank' => $pencairanValidasi["kd_bank"],
			'no_rek_tujuan' => $pencairanValidasi["no_rek_tujuan"],
			'atas_nama' => strtoupper($pencairanValidasi["atas_nama"]),
		]);
		$this->pencairanModel->transCommit();
		if ($this->pencairanModel->transStatus() === false) {
			return json_encode([
				'success' => false,
				'msg' => 'Gagal melakukan verifikasi pencairan'
			]);
		}else {
			$this->session->setFlashdata('sukses', $status == 1 ? 'Pengajuan pencairan diverifikasi' : 'pengajuan pencairan ditangguhkan'); // tampilkan toast ke aplikasi
			$this->sendNotifToRider($kd_driver, $pesan, 4); //kirim notif ke rider
			return json_encode([
				'success' => true,
				'redirect' => base_url('rider/pencairan'),
			]);
		}
		}else {
			$update = $this->pencairanModel->query("UPDATE t_penarikan_validasi SET status = -1 WHERE no_transaksi = '$no_transaksi'");
			$this->session->setFlashdata('sukses', $status == 1 ? 'Pengajuan pencairan diverifikasi' : 'pengajuan pencairan ditangguhkan'); // tampilkan toast ke aplikasi
			$this->sendNotifToRider($kd_driver, $pesan, 4); //kirim notif ke rider
			return json_encode([
				'success' => true,
				'redirect' => base_url('rider/pencairan'),
			]);
		}
		
	}


	// fungsi utk kirim notif ke aplikasi rider
	public function sendNotifToRider($kd_driver, $pesan, $jenis = 0)
	{
		$payload = array(
			'to' => '/topics/kongVal',
			'priority' => 'high',
			"mutable_content" => true,
			'data' => array(
				"id_dr" => $kd_driver,
				"psn" => $pesan,
				"mode" => '1',
				"jenis_notif" => $jenis
			),
		);
		$headers = array(
			'Authorization:key=AAAAJrZwZQg:APA91bEp4BYq1kZcVwUyuh02a_s5F3txxf_CJHNbvdwsdjs6qwdHuWIiS3BKN7ETR3gtQkVZgHebKCH4C6N-QaHeJTEC5m8pMT0MDD5i6oG2bqPwbPT3XR3dY9h_zku1TtamNt9_Tn9q', 'Content-Type: application/json',
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		$result = curl_exec($ch);
		curl_close($ch);
	}

	public function getKendaraan($jenis = null)
	{
		$search = $this->request->getPost('search')['value'];
		$order = !empty($this->request->getPost('order')) ? $this->request->getPost('order') : '';
		$start = $this->request->getPost('start');
		$limit = $this->request->getPost('length');

		$result = $this->riderModel->getKendaraan($search, $start, $limit, null, $jenis)->getResult();
		$totalCount = count($this->riderModel->getRider($search, '', '', '', $jenis)->getResultArray());

		$no = $start + 1;
		$data = [];

		foreach ($result as $key => $value) {
			switch ($value->status) {
				case '0':
					$status = 'non aktif';
					$text = 'dark';
					break;
				case '1':
					$status = 'aktif';
					$text = 'success';
					break;
				case '2':
					$status = 'sedang digunakan';
					$text = 'indigo';
					break;
				case '-1':
					$status = 'Banned';
					$text = 'warning';
					break;

				default:
					$status = 'Nonaktif';
					$text = 'gray';
					break;
			}

			$data[$key] = [
				$no,
				$value->nomor_plat,
				$value->merk_nama,
				$value->model_nama,
				$value->nama_depan,
				$value->tahun_pembuatan,
				'<span class="' . $text . ' font-weight-bold">' . $status . '</span>',
				'<a href="' . base_url('rider/detail/' . $value->kd_kendaraan) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>',
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
}