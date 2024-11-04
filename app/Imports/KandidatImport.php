<?php

namespace App\Imports;

use App\Models\Kandidat;
use App\Models\Posisi;
use App\Models\Wilayah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KandidatImport implements ToCollection, WithHeadingRow
{
    protected $tanggal;
    private $allowedposisi = [];

    private $allowedwilayah = [];
    private $errors = [];
    private $rowNumber;

    public function __construct($tanggal)
    {
        $this->tanggal = $tanggal;
        $this->rowNumber = 2;
        $this->allowedposisi = Posisi::pluck('nama_posisi')->toArray();
        $this->allowedwilayah = Wilayah::pluck('nama_wilayah')->toArray();
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {

        foreach ($rows as $row) {
            $tanggallamar = $this->tanggal;
            list($tahun, $bulan, $hari) = explode("-", $tanggallamar);

            // Debugging
            $posisi = $row['position'];
            $wilayah = $row['wilayah'];

            $contact = $row['contact'];
            $contactedited = preg_replace('/^\+62/', '0', $contact);
            
        
           

            if ($posisi !== null && !in_array(strtolower(trim($posisi)), array_map('strtolower', $this->allowedposisi))) {
                $this->errors[] = "Posisi pada baris {$this->rowNumber} tidak valid, sesuaikan dengan master data posisi.";
                continue;
            }

            if ($wilayah !== null && !in_array(strtolower(trim($wilayah)), array_map('strtolower', $this->allowedwilayah))) {
                $this->errors[] = "Wilayah pada baris {$this->rowNumber} tidak valid, sesuaikan dengan master data wilayah.";
                continue;
            }
            

            $dataposisi = Posisi::where('nama_posisi', $posisi)->first();
            $posisiid = $dataposisi->id;


            $datawilayah = Wilayah::where('nama_wilayah', $wilayah)->first();
            $wilayahid = $datawilayah->id;


            if (
                empty($row['position']) &&
                empty($row['wilayah']) &&
                empty($row['applicant_name']) && 
                empty($row['contact']) &&
                empty($row['user_email'])
            ) {
                continue; 
            }

            $loggedInUser = auth()->user();
            $loggedInUsername = $loggedInUser->nama; 
            $userid = $loggedInUser->id;

            Kandidat::create([
                'tanggal' => $tanggallamar,
                'hari' => $hari,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nama_kandidat' => $row['applicant_name'],
                'posisi' => $posisi,
                'no_hp' => $row['contact'],
                'email' => $row['user_email'],
                'posisi_id' => $posisiid,
                // 'sumber_id' => $sumber,
                'wilayah_id' => $wilayahid,
                'status_hire' => "Belum Diproses",
                'created_by' => $loggedInUsername,
                'user_id' => $userid,
            ]);
        }

    }
}
