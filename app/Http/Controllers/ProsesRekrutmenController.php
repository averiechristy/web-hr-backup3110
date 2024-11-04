<?php

namespace App\Http\Controllers;

use App\Models\DetailPosisi;
use App\Models\Kandidat;
use App\Models\LogTahapan;
use App\Models\Posisi;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class ProsesRekrutmenController extends Controller
{
    /**
     * Display a listing of the resource.
     */


     public function belumprosesindex(Request $request){

        $roleid = auth()->user()->role_id;

        if($roleid == 2){

            $userId = auth()->id();
            $detailPosisi = DetailPosisi::where('user_id', $userId)->get();
        
            $assignedPosisi = $detailPosisi->pluck('posisi_id')->unique();
            $assignedWilayah = $detailPosisi->pluck('wilayah_id')->unique();
            
            $assignedPosisiIds = [];
            $assignedWilayahIds = [];
            
            foreach ($assignedPosisi as $posisi) {
                $assignedPosisiIds = array_merge($assignedPosisiIds, explode(',', $posisi));
            }
            
            foreach ($assignedWilayah as $wilayah) {
                $assignedWilayahIds = array_merge($assignedWilayahIds, explode(',', $wilayah));
            }
            
            $assignedPosisiIds = array_unique($assignedPosisiIds);
            $assignedWilayahIds = array_unique($assignedWilayahIds);
            
            // Initialize the query for Kandidat
            $query = Kandidat::query();
        
            // Filtering by position and region based on assigned positions and regions
            $query->where(function ($query) use ($assignedPosisiIds, $assignedWilayahIds) {
                foreach ($assignedPosisiIds as $posisiId) {
                    foreach ($assignedWilayahIds as $wilayahId) {
                        $query->orWhere(function ($query) use ($posisiId, $wilayahId) {
                            $query->where('posisi_id', $posisiId)
                                  ->where('wilayah_id', $wilayahId);
                        });
                    }
                }
            });
              // Filtering by user input
              if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $selectedPosisiId = $request->filter_posisi;
                $query->where('posisi_id', $selectedPosisiId);
        
                // Get assigned wilayah for the selected posisi
                $assignedWilayahIdsForPosisi = $detailPosisi->where('posisi_id', $selectedPosisiId)->pluck('wilayah_id')->toArray();
                $wilayah = Wilayah::whereIn('id', explode(',', implode(',', $assignedWilayahIdsForPosisi)))->get();
                
                // Apply wilayah filter if specified
                if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                    $query->where('wilayah_id', $request->filter_wilayah);
                }
            } else {
                // If no posisi is selected, no wilayah should be shown
                $wilayah = collect(); // Empty collection
            }

            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Belum Diproses')->get();
            $posisi = Posisi::whereIn('id', $assignedPosisiIds)->get();

           return view('superadmin.belumproses.index',[
            'kandidat' => $kandidat,
            'posisi' => $posisi,
            'wilayah' => $wilayah,
            'selectedPosisi' => $request->filter_posisi,
            'selectedWilayah' => $request->filter_wilayah,
         
           ]);


        }else {

            $posisi = Posisi::all()->sortBy('nama_posisi');
            $wilayah = Wilayah::all()->sortBy('nama_wilayah');
            $query = Kandidat::query();
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $query->where('posisi_id', $request->filter_posisi);
            }
    
            if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                $query->where('wilayah_id', $request->filter_wilayah);
            }
    
            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Belum Diproses')->get();
            return view('superadmin.belumproses.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
              
               ]);
        }

     }


     public function process(Request $request)
     {
      
         $data = $request->all();
         
         $checkedIds = explode(',', $data['checked_ids']);
         
         $status = $data['status'];
         
        
         foreach ($checkedIds as $id) {
            
             $kandidat = Kandidat::find($id);
             $posisiid = $kandidat->posisi_id;
             $wilayahid = $kandidat ->wilayah_id;

             if ($kandidat) {
                $now = now();  // Mengambil waktu saat ini
                $bulan = $now->format('m'); // Mendapatkan bulan
                $tahun = $now->format('Y'); // Mendapatkan tahun
                 $kandidat->status_hire = $status;
                 $kandidat->save();

                 LogTahapan::create([
                    'kandidat_id' => $id,
                    'status_tahapan' => $status, 
                    'tanggal' => $now,  // Menyimpan waktu lengkap
                    'bulan' => $bulan,  // Menyimpan bulan
                    'tahun' => $tahun,  // Menyimpan tahun
                    'posisi_id' => $posisiid,
                    'wilayah_id' => $wilayahid
                ]);
             }
         }
         
        
         $request->session()->flash('success', 'Status kandidat berhasil diubah.');

         return redirect(route('superadmin.belumproses.index'));
     }
     


     public function psikotesindex(Request $request){

        $roleid = auth()->user()->role_id;

     

        if($roleid == 2){

            $userId = auth()->id();
            $detailPosisi = DetailPosisi::where('user_id', $userId)->get();
        
            $assignedPosisi = $detailPosisi->pluck('posisi_id')->unique();
            $assignedWilayah = $detailPosisi->pluck('wilayah_id')->unique();
            
            $assignedPosisiIds = [];
            $assignedWilayahIds = [];
            
            foreach ($assignedPosisi as $posisi) {
                $assignedPosisiIds = array_merge($assignedPosisiIds, explode(',', $posisi));
            }
            
            foreach ($assignedWilayah as $wilayah) {
                $assignedWilayahIds = array_merge($assignedWilayahIds, explode(',', $wilayah));
            }
            
            $assignedPosisiIds = array_unique($assignedPosisiIds);
            $assignedWilayahIds = array_unique($assignedWilayahIds);
            
            // Initialize the query for Kandidat
            $query = Kandidat::query();
        
            // Filtering by position and region based on assigned positions and regions
            $query->where(function ($query) use ($assignedPosisiIds, $assignedWilayahIds) {
                foreach ($assignedPosisiIds as $posisiId) {
                    foreach ($assignedWilayahIds as $wilayahId) {
                        $query->orWhere(function ($query) use ($posisiId, $wilayahId) {
                            $query->where('posisi_id', $posisiId)
                                  ->where('wilayah_id', $wilayahId);
                        });
                    }
                }
            });
              // Filtering by user input
              if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $selectedPosisiId = $request->filter_posisi;
                $query->where('posisi_id', $selectedPosisiId);
        
                // Get assigned wilayah for the selected posisi
                $assignedWilayahIdsForPosisi = $detailPosisi->where('posisi_id', $selectedPosisiId)->pluck('wilayah_id')->toArray();
                $wilayah = Wilayah::whereIn('id', explode(',', implode(',', $assignedWilayahIdsForPosisi)))->get();
                
                // Apply wilayah filter if specified
                if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                    $query->where('wilayah_id', $request->filter_wilayah);
                }
            } else {
                // If no posisi is selected, no wilayah should be shown
                $wilayah = collect(); // Empty collection
            }

            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Psikotes')->get();
            $posisi = Posisi::whereIn('id', $assignedPosisiIds)->get();
            return view('superadmin.psikotes.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
           ]);


        }else {

            $posisi = Posisi::all()->sortBy('nama_posisi');
            $wilayah = Wilayah::all()->sortBy('nama_wilayah');
            $query = Kandidat::query();
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $query->where('posisi_id', $request->filter_posisi);
            }
    
            if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                $query->where('wilayah_id', $request->filter_wilayah);
            }
    
            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Psikotes')->get();
            return view('superadmin.psikotes.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
              
               ]);
        }

     }


     public function psikotesprocess(Request $request)
     {
         $data = $request->all();
         
         $checkedIds = explode(',', $data['checked_ids']);
         
         $status = $data['status'];
         
        
         foreach ($checkedIds as $id) {
            
             $kandidat = Kandidat::find($id);
             $posisiid = $kandidat->posisi_id;
             $wilayahid = $kandidat ->wilayah_id;

             if ($kandidat) {
                $now = now();  // Mengambil waktu saat ini
                $bulan = $now->format('m'); // Mendapatkan bulan
                $tahun = $now->format('Y'); // Mendapatkan tahun
                 $kandidat->status_hire = $status;
                 $kandidat->save();

                LogTahapan::create([
                    'kandidat_id' => $id,
                    'status_tahapan' => $status, 
                    'tanggal' => $now,  // Menyimpan waktu lengkap
                    'bulan' => $bulan,  // Menyimpan bulan
                    'tahun' => $tahun,  // Menyimpan tahun
                    'posisi_id' => $posisiid,
                    'wilayah_id' => $wilayahid
                ]);

             }
         }
                 
         $request->session()->flash('success', 'Status kandidat berhasil diubah.');

         return redirect(route('superadmin.psikotes.index'));
     }


     public function itvhrindex(Request $request){

        $roleid = auth()->user()->role_id;

        if($roleid == 2){

            $userId = auth()->id();
            $detailPosisi = DetailPosisi::where('user_id', $userId)->get();
        
            $assignedPosisi = $detailPosisi->pluck('posisi_id')->unique();
            $assignedWilayah = $detailPosisi->pluck('wilayah_id')->unique();
            
            $assignedPosisiIds = [];
            $assignedWilayahIds = [];
            
            foreach ($assignedPosisi as $posisi) {
                $assignedPosisiIds = array_merge($assignedPosisiIds, explode(',', $posisi));
            }
            
            foreach ($assignedWilayah as $wilayah) {
                $assignedWilayahIds = array_merge($assignedWilayahIds, explode(',', $wilayah));
            }
            
            $assignedPosisiIds = array_unique($assignedPosisiIds);
            $assignedWilayahIds = array_unique($assignedWilayahIds);
            
            
            $query = Kandidat::query();
                    
            $query->where(function ($query) use ($assignedPosisiIds, $assignedWilayahIds) {
                foreach ($assignedPosisiIds as $posisiId) {
                    foreach ($assignedWilayahIds as $wilayahId) {
                        $query->orWhere(function ($query) use ($posisiId, $wilayahId) {
                            $query->where('posisi_id', $posisiId)
                                  ->where('wilayah_id', $wilayahId);
                        });
                    }
                }
            });
             
              if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $selectedPosisiId = $request->filter_posisi;
                $query->where('posisi_id', $selectedPosisiId);
        
                
                $assignedWilayahIdsForPosisi = $detailPosisi->where('posisi_id', $selectedPosisiId)->pluck('wilayah_id')->toArray();
                $wilayah = Wilayah::whereIn('id', explode(',', implode(',', $assignedWilayahIdsForPosisi)))->get();
                
                
                if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                    $query->where('wilayah_id', $request->filter_wilayah);
                }
            } else {
                
                $wilayah = collect(); 
            }

            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Interview HR')->get();
            $posisi = Posisi::whereIn('id', $assignedPosisiIds)->get();
            return view('superadmin.itvhr.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
           ]);


        }else {

            $posisi = Posisi::all()->sortBy('nama_posisi');
            $wilayah = Wilayah::all()->sortBy('nama_wilayah');
            $query = Kandidat::query();
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $query->where('posisi_id', $request->filter_posisi);
            }
    
            if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                $query->where('wilayah_id', $request->filter_wilayah);
            }
    
            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Interview HR')->get();
            return view('superadmin.itvhr.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
              
               ]);
        }

     }

     public function itvhrprocess(Request $request)
     {
         $data = $request->all();
         $checkedIds = explode(',', $data['checked_ids']);
         $status = $data['status'];
     
         foreach ($checkedIds as $id) {
             $kandidat = Kandidat::find($id);
             $posisiid = $kandidat->posisi_id;
             $wilayahid = $kandidat ->wilayah_id;

             if ($kandidat) {
                 $now = now();  // Mengambil waktu saat ini
                 $bulan = $now->format('m'); // Mendapatkan bulan
                 $tahun = $now->format('Y'); // Mendapatkan tahun
                 $kandidat->status_hire = $status;
                 $kandidat->save();
     
                 // Create a new log for the current stage
                 LogTahapan::create([
                     'kandidat_id' => $id,
                     'status_tahapan' => $status, 
                     'tanggal' => $now,  // Menyimpan waktu lengkap
                     'bulan' => $bulan,  // Menyimpan bulan
                     'tahun' => $tahun,  // Menyimpan tahun
                     'posisi_id' => $posisiid,
                     'wilayah_id' => $wilayahid
                 ]);
     
                 // If the current status is Interview User, Training, Tandem, or Lolos,
                 // find the "Interview HR" log and update its flag_lolos to "Yes"
                 if (in_array($status, ['Interview User', 'Training', 'Tandem', 'Lolos'])) {
                     $logInterviewHR = LogTahapan::where('kandidat_id', $id)
                         ->where('status_tahapan', 'Interview HR')
                         ->first();
     
                     if ($logInterviewHR) {
                         $logInterviewHR->flag_lolos = 'Yes';
                         $logInterviewHR->save();
                     }
                 }
             }
         }
     
         $request->session()->flash('success', 'Status kandidat berhasil diubah.');
         return redirect(route('superadmin.itvhr.index'));
     }
     


     
     public function itvuserindex(Request $request){

        $roleid = auth()->user()->role_id;

     

        if($roleid == 2){

            $userId = auth()->id();
            $detailPosisi = DetailPosisi::where('user_id', $userId)->get();
        
            $assignedPosisi = $detailPosisi->pluck('posisi_id')->unique();
            $assignedWilayah = $detailPosisi->pluck('wilayah_id')->unique();
            
            $assignedPosisiIds = [];
            $assignedWilayahIds = [];
            
            foreach ($assignedPosisi as $posisi) {
                $assignedPosisiIds = array_merge($assignedPosisiIds, explode(',', $posisi));
            }
            
            foreach ($assignedWilayah as $wilayah) {
                $assignedWilayahIds = array_merge($assignedWilayahIds, explode(',', $wilayah));
            }
            
            $assignedPosisiIds = array_unique($assignedPosisiIds);
            $assignedWilayahIds = array_unique($assignedWilayahIds);
            
            // Initialize the query for Kandidat
            $query = Kandidat::query();
        
            // Filtering by position and region based on assigned positions and regions
            $query->where(function ($query) use ($assignedPosisiIds, $assignedWilayahIds) {
                foreach ($assignedPosisiIds as $posisiId) {
                    foreach ($assignedWilayahIds as $wilayahId) {
                        $query->orWhere(function ($query) use ($posisiId, $wilayahId) {
                            $query->where('posisi_id', $posisiId)
                                  ->where('wilayah_id', $wilayahId);
                        });
                    }
                }
            });
              // Filtering by user input
              if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $selectedPosisiId = $request->filter_posisi;
                $query->where('posisi_id', $selectedPosisiId);
        
                // Get assigned wilayah for the selected posisi
                $assignedWilayahIdsForPosisi = $detailPosisi->where('posisi_id', $selectedPosisiId)->pluck('wilayah_id')->toArray();
                $wilayah = Wilayah::whereIn('id', explode(',', implode(',', $assignedWilayahIdsForPosisi)))->get();
                
                // Apply wilayah filter if specified
                if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                    $query->where('wilayah_id', $request->filter_wilayah);
                }
            } else {
                // If no posisi is selected, no wilayah should be shown
                $wilayah = collect(); // Empty collection
            }

            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Interview User')->get();
            $posisi = Posisi::whereIn('id', $assignedPosisiIds)->get();
            return view('superadmin.itvuser.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
           ]);


        }else {

            $posisi = Posisi::all()->sortBy('nama_posisi');
            $wilayah = Wilayah::all()->sortBy('nama_wilayah');
            $query = Kandidat::query();
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $query->where('posisi_id', $request->filter_posisi);
            }
    
            if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                $query->where('wilayah_id', $request->filter_wilayah);
            }
    
            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Interview User')->get();
            return view('superadmin.itvuser.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
              
               ]);
        }

     }



     public function itvuserprocess(Request $request)
     {

         $data = $request->all();

         $checkedIds = explode(',', $data['checked_ids']);
         
         $status = $data['status'];
         
        
         foreach ($checkedIds as $id) {
            
             $kandidat = Kandidat::find($id);
             $posisiid = $kandidat->posisi_id;
             $wilayahid = $kandidat ->wilayah_id;

             
             if ($kandidat) {
                $now = now();  // Mengambil waktu saat ini
                $bulan = $now->format('m'); // Mendapatkan bulan
                $tahun = $now->format('Y'); // Mendapatkan tahun
                 $kandidat->status_hire = $status;
                 $kandidat->save();

                 LogTahapan::create([
                    'kandidat_id' => $id,
                    'status_tahapan' => $status, 
                    'tanggal' => $now,  // Menyimpan waktu lengkap
                    'bulan' => $bulan,  // Menyimpan bulan
                    'tahun' => $tahun,  // Menyimpan tahun
                    'posisi_id' => $posisiid,
                    'wilayah_id' => $wilayahid
                ]);
             }
         }
         
         $request->session()->flash('success', 'Status kandidat berhasil diubah.');

         return redirect(route('superadmin.itvuser.index'));
     }



     public function tandemindex(Request $request){

        $roleid = auth()->user()->role_id;

        if($roleid == 2){

            $userId = auth()->id();
            $detailPosisi = DetailPosisi::where('user_id', $userId)->get();
        
            $assignedPosisi = $detailPosisi->pluck('posisi_id')->unique();
            $assignedWilayah = $detailPosisi->pluck('wilayah_id')->unique();
            
            $assignedPosisiIds = [];
            $assignedWilayahIds = [];
            
            foreach ($assignedPosisi as $posisi) {
                $assignedPosisiIds = array_merge($assignedPosisiIds, explode(',', $posisi));
            }
            
            foreach ($assignedWilayah as $wilayah) {
                $assignedWilayahIds = array_merge($assignedWilayahIds, explode(',', $wilayah));
            }
            
            $assignedPosisiIds = array_unique($assignedPosisiIds);
            $assignedWilayahIds = array_unique($assignedWilayahIds);
            
            // Initialize the query for Kandidat
            $query = Kandidat::query();
        
            // Filtering by position and region based on assigned positions and regions
            $query->where(function ($query) use ($assignedPosisiIds, $assignedWilayahIds) {
                foreach ($assignedPosisiIds as $posisiId) {
                    foreach ($assignedWilayahIds as $wilayahId) {
                        $query->orWhere(function ($query) use ($posisiId, $wilayahId) {
                            $query->where('posisi_id', $posisiId)
                                  ->where('wilayah_id', $wilayahId);
                        });
                    }
                }
            });

              // Filtering by user input
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $selectedPosisiId = $request->filter_posisi;
                $query->where('posisi_id', $selectedPosisiId);
        
                // Get assigned wilayah for the selected posisi
                $assignedWilayahIdsForPosisi = $detailPosisi->where('posisi_id', $selectedPosisiId)->pluck('wilayah_id')->toArray();
                $wilayah = Wilayah::whereIn('id', explode(',', implode(',', $assignedWilayahIdsForPosisi)))->get();
                
                // Apply wilayah filter if specified
                if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                    $query->where('wilayah_id', $request->filter_wilayah);
                }
                
            } else {
                // If no posisi is selected, no wilayah should be shown
                $wilayah = collect(); // Empty collection
            }

            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Tandem')->get();
            $posisi = Posisi::whereIn('id', $assignedPosisiIds)->get();
            return view('superadmin.tandem.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
           ]);


        }else {

            $posisi = Posisi::all()->sortBy('nama_posisi');
            $wilayah = Wilayah::all()->sortBy('nama_wilayah');
            $query = Kandidat::query();
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $query->where('posisi_id', $request->filter_posisi);
            }
    
            if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                $query->where('wilayah_id', $request->filter_wilayah);
            }
    
            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Tandem')->get();
            return view('superadmin.tandem.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
              
               ]);
        }

     }


     public function tandemprocess(Request $request)
     {

         $data = $request->all();

         $checkedIds = explode(',', $data['checked_ids']);
         
         $status = $data['status'];
         
        
         foreach ($checkedIds as $id) {
            
             $kandidat = Kandidat::find($id);
             $posisiid = $kandidat->posisi_id;
             $wilayahid = $kandidat ->wilayah_id;

             
             if ($kandidat) {
                $now = now();  // Mengambil waktu saat ini
                $bulan = $now->format('m'); // Mendapatkan bulan
                $tahun = $now->format('Y'); // Mendapatkan tahun
                 $kandidat->status_hire = $status;
                 $kandidat->save();

                 LogTahapan::create([
                    'kandidat_id' => $id,
                    'status_tahapan' => $status, 
                    'tanggal' => $now,  // Menyimpan waktu lengkap
                    'bulan' => $bulan,  // Menyimpan bulan
                    'tahun' => $tahun,  // Menyimpan tahun
                    'posisi_id' => $posisiid,
                    'wilayah_id' => $wilayahid
                    
                ]);
             }
         }
         
         $request->session()->flash('success', 'Status kandidat berhasil diubah.');

         return redirect(route('superadmin.tandem.index'));
     }


     public function saveindex(Request $request){

        $roleid = auth()->user()->role_id;
        

        if($roleid == 2){

            $userId = auth()->id();
            $detailPosisi = DetailPosisi::where('user_id', $userId)->get();
        
            $assignedPosisi = $detailPosisi->pluck('posisi_id')->unique();
            $assignedWilayah = $detailPosisi->pluck('wilayah_id')->unique();
            
            $assignedPosisiIds = [];
            $assignedWilayahIds = [];
            
            foreach ($assignedPosisi as $posisi) {
                $assignedPosisiIds = array_merge($assignedPosisiIds, explode(',', $posisi));
            }
            
            foreach ($assignedWilayah as $wilayah) {
                $assignedWilayahIds = array_merge($assignedWilayahIds, explode(',', $wilayah));
            }
            
            $assignedPosisiIds = array_unique($assignedPosisiIds);
            $assignedWilayahIds = array_unique($assignedWilayahIds);
            
            // Initialize the query for Kandidat
            $query = Kandidat::query();
        
            // Filtering by position and region based on assigned positions and regions
            $query->where(function ($query) use ($assignedPosisiIds, $assignedWilayahIds) {
                foreach ($assignedPosisiIds as $posisiId) {
                    foreach ($assignedWilayahIds as $wilayahId) {
                        $query->orWhere(function ($query) use ($posisiId, $wilayahId) {
                            $query->where('posisi_id', $posisiId)
                                  ->where('wilayah_id', $wilayahId);
                        });
                    }
                }
            });

              // Filtering by user input
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $selectedPosisiId = $request->filter_posisi;
                $query->where('posisi_id', $selectedPosisiId);
        
                // Get assigned wilayah for the selected posisi
                $assignedWilayahIdsForPosisi = $detailPosisi->where('posisi_id', $selectedPosisiId)->pluck('wilayah_id')->toArray();
                $wilayah = Wilayah::whereIn('id', explode(',', implode(',', $assignedWilayahIdsForPosisi)))->get();
                
                // Apply wilayah filter if specified
                if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                    $query->where('wilayah_id', $request->filter_wilayah);
                }
                
            } else {
                // If no posisi is selected, no wilayah should be shown
                $wilayah = collect(); // Empty collection
            }

            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Simpan Kandidat')->get();
            $posisi = Posisi::whereIn('id', $assignedPosisiIds)->get();
            return view('superadmin.save.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
           ]);


        }else {

            $posisi = Posisi::all()->sortBy('nama_posisi');
            $wilayah = Wilayah::all()->sortBy('nama_wilayah');
            $query = Kandidat::query();
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $query->where('posisi_id', $request->filter_posisi);
            }
    
            if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                $query->where('wilayah_id', $request->filter_wilayah);
            }
    
            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Simpan Kandidat')->get();
            return view('superadmin.save.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
              
               ]);
        }

     }
     
     public function mundurkanStatus(Request $request)
     {  
        
        $kandidatid = $request -> kandidat_id;
        $statusmundur = $request->status;

        $datalogtahapan = LogTahapan::where('status_tahapan','Psikotes')
        ->where('kandidat_id', $kandidatid)
        ->first();

        $datalogtahapan->delete();

        $datakandidat = Kandidat::find($kandidatid);
        $datakandidat -> status_hire = $statusmundur;
        $datakandidat->save();

        $request->session()->flash('success', 'Status kandidat berhasil dimundurkan.');

        return redirect(route('superadmin.psikotes.index'));
     }


     public function itvhrmundurkanStatus(Request $request){

        $kandidatid = $request -> kandidat_id;
        $statusmundur = $request->status;

        $datalogtahapan = LogTahapan::where('status_tahapan','Interview HR')
        ->where('kandidat_id', $kandidatid)
        ->first();

        $datalogtahapan->delete();

        $datakandidat = Kandidat::find($kandidatid);
        $datakandidat -> status_hire = $statusmundur;
        $datakandidat->save();

        $request->session()->flash('success', 'Status kandidat berhasil dimundurkan.');

        return redirect(route('superadmin.itvhr.index'));
        
     }

     public function itvusermundurkanStatus(Request $request) {

        $kandidatid = $request->kandidat_id;
        $statusmundur = $request->status;

        $datalogtahapan = LogTahapan::where('status_tahapan', 'Interview User' )
        ->where('kandidat_id', $kandidatid)
        ->first();

        $datalogtahapan -> delete();

        $datakandidat = Kandidat::find($kandidatid);
        $datakandidat -> status_hire = $statusmundur;
        $datakandidat->save();

        $request->session()->flash('success', 'Status kandidat berhasil dimundurkan.');

        return redirect(route('superadmin.itvuser.index'));
     }

     public function trainingmundurkanStatus(Request $request){

        $kandidatid = $request->kandidat_id;
        $statusmundur = $request -> status;
        
        $datalogtahapan = LogTahapan::where('status_tahapan', 'Training')
        ->where('kandidat_id', $kandidatid)
        ->first();

        $datalogtahapan ->delete();

        $datakandidat = Kandidat::find($kandidatid);
        $datakandidat -> status_hire = $statusmundur;
        $datakandidat->save();

        $request->session()->flash('success', 'Status kandidat berhasil dimundurkan.');

        return redirect(route('superadmin.training.index'));

     }

     public function tandemmundurkanStatus(Request $request) {

        $kandidatid = $request->kandidat_id;
        $statusmundur = $request->status;

        $datalogtahapan = LogTahapan::where('status_tahapan', 'Tandem')
        ->where('kandidat_id', $kandidatid)
        ->first();

        $datalogtahapan -> delete();
        $datakandidat = Kandidat::find($kandidatid);
        $datakandidat -> status_hire = $statusmundur;
        $datakandidat->save();

        $request->session()->flash('success', 'Status kandidat berhasil dimundurkan.');

        return redirect(route('superadmin.tandem.index'));

     }


     public function lolosmundurkanStatus(Request $request){

        $kandidatid = $request->kandidat_id;
        $statusmundur = $request->status;
        
        $datalogtahapan = LogTahapan::where('status_tahapan', 'Lolos')
        ->where('kandidat_id', $kandidatid)
        ->first();

        $datalogtahapan->delete();

        $datakandidat = Kandidat::find($kandidatid);
        $datakandidat -> status_hire = $statusmundur;
        $datakandidat->save();

        $request->session()->flash('success', 'Status kandidat berhasil dimundurkan.');

        return redirect(route('superadmin.lolos.index'));

     }


     
     public function tidaklolosmundurkanStatus(Request $request){

        $kandidatid = $request->kandidat_id;
        $statusmundur = $request->status;

        $datalogtahapan = LogTahapan::where('status_tahapan', 'Tidak Lolos')
        ->where('kandidat_id', $kandidatid)
        ->first();
        
        $datalogtahapan->delete();

        $datakandidat = Kandidat::find($kandidatid);
        $datakandidat->status_hire = $statusmundur;
        $datakandidat->save();

        $request->session()->flash('success', 'Status kandidat berhasil dimundurkan.');

        return redirect(route('superadmin.tidaklolos.index'));

     }

     public function trainingindex(Request $request){

        $roleid = auth()->user()->role_id;

    
        if($roleid == 2){

            $userId = auth()->id();
            $detailPosisi = DetailPosisi::where('user_id', $userId)->get();
        
            $assignedPosisi = $detailPosisi->pluck('posisi_id')->unique();
            $assignedWilayah = $detailPosisi->pluck('wilayah_id')->unique();
            
            $assignedPosisiIds = [];
            $assignedWilayahIds = [];
            
            foreach ($assignedPosisi as $posisi) {
                $assignedPosisiIds = array_merge($assignedPosisiIds, explode(',', $posisi));
            }
            
            foreach ($assignedWilayah as $wilayah) {
                $assignedWilayahIds = array_merge($assignedWilayahIds, explode(',', $wilayah));
            }
            
            $assignedPosisiIds = array_unique($assignedPosisiIds);
            $assignedWilayahIds = array_unique($assignedWilayahIds);
            
            // Initialize the query for Kandidat
            $query = Kandidat::query();
        
            // Filtering by position and region based on assigned positions and regions
            $query->where(function ($query) use ($assignedPosisiIds, $assignedWilayahIds) {
                foreach ($assignedPosisiIds as $posisiId) {
                    foreach ($assignedWilayahIds as $wilayahId) {
                        $query->orWhere(function ($query) use ($posisiId, $wilayahId) {
                            $query->where('posisi_id', $posisiId)
                                  ->where('wilayah_id', $wilayahId);
                        });
                    }
                }
            });
              // Filtering by user input
              if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $selectedPosisiId = $request->filter_posisi;
                $query->where('posisi_id', $selectedPosisiId);
        
                // Get assigned wilayah for the selected posisi
                $assignedWilayahIdsForPosisi = $detailPosisi->where('posisi_id', $selectedPosisiId)->pluck('wilayah_id')->toArray();
                $wilayah = Wilayah::whereIn('id', explode(',', implode(',', $assignedWilayahIdsForPosisi)))->get();
                
                // Apply wilayah filter if specified
                if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                    $query->where('wilayah_id', $request->filter_wilayah);
                }
            } else {
                // If no posisi is selected, no wilayah should be shown
                $wilayah = collect(); // Empty collection
            }

            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Training')->get();
            $posisi = Posisi::whereIn('id', $assignedPosisiIds)->get();
            return view('superadmin.training.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
           ]);


        }else {
            
            $posisi = Posisi::all()->sortBy('nama_posisi');
            $wilayah = Wilayah::all()->sortBy('nama_wilayah');
            $query = Kandidat::query();
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $query->where('posisi_id', $request->filter_posisi);
            }
    
            if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                $query->where('wilayah_id', $request->filter_wilayah);
            }
    
            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Training')->get();
            return view('superadmin.training.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
              
               ]);
        }

     }

     public function trainingprocess(Request $request)
     {

         $data = $request->all();

         $checkedIds = explode(',', $data['checked_ids']);
         
         $status = $data['status'];
         
        
         foreach ($checkedIds as $id) {
            
             $kandidat = Kandidat::find($id);
             $posisiid = $kandidat->posisi_id;
             $wilayahid = $kandidat ->wilayah_id;

             
             if ($kandidat) {
                $now = now();  // Mengambil waktu saat ini
                $bulan = $now->format('m'); // Mendapatkan bulan
                $tahun = $now->format('Y'); // Mendapatkan tahun
                 $kandidat->status_hire = $status;
                 $kandidat->save();

                 LogTahapan::create([
                    'kandidat_id' => $id,
                    'status_tahapan' => $status, 
                    'tanggal' => $now,  // Menyimpan waktu lengkap
                    'bulan' => $bulan,  // Menyimpan bulan
                    'tahun' => $tahun,  // Menyimpan tahun
                    'posisi_id' => $posisiid,
                    'wilayah_id' => $wilayahid
                ]);
             }
         }
         
         $request->session()->flash('success', 'Status kandidat berhasil diubah.');

         return redirect(route('superadmin.training.index'));
     }


     public function lolosindex(Request $request){

        $roleid = auth()->user()->role_id;

    
        if($roleid == 2){

            $userId = auth()->id();
            $detailPosisi = DetailPosisi::where('user_id', $userId)->get();
        
            $assignedPosisi = $detailPosisi->pluck('posisi_id')->unique();
            $assignedWilayah = $detailPosisi->pluck('wilayah_id')->unique();
            
            $assignedPosisiIds = [];
            $assignedWilayahIds = [];
            
            foreach ($assignedPosisi as $posisi) {
                $assignedPosisiIds = array_merge($assignedPosisiIds, explode(',', $posisi));
            }
            
            foreach ($assignedWilayah as $wilayah) {
                $assignedWilayahIds = array_merge($assignedWilayahIds, explode(',', $wilayah));
            }
            
            $assignedPosisiIds = array_unique($assignedPosisiIds);
            $assignedWilayahIds = array_unique($assignedWilayahIds);
            
            // Initialize the query for Kandidat
            $query = Kandidat::query();
        
            // Filtering by position and region based on assigned positions and regions
            $query->where(function ($query) use ($assignedPosisiIds, $assignedWilayahIds) {
                foreach ($assignedPosisiIds as $posisiId) {
                    foreach ($assignedWilayahIds as $wilayahId) {
                        $query->orWhere(function ($query) use ($posisiId, $wilayahId) {
                            $query->where('posisi_id', $posisiId)
                                  ->where('wilayah_id', $wilayahId);
                        });
                    }
                }
            });
              // Filtering by user input
              if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $selectedPosisiId = $request->filter_posisi;
                $query->where('posisi_id', $selectedPosisiId);
        
                // Get assigned wilayah for the selected posisi
                $assignedWilayahIdsForPosisi = $detailPosisi->where('posisi_id', $selectedPosisiId)->pluck('wilayah_id')->toArray();
                $wilayah = Wilayah::whereIn('id', explode(',', implode(',', $assignedWilayahIdsForPosisi)))->get();
                
                // Apply wilayah filter if specified
                if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                    $query->where('wilayah_id', $request->filter_wilayah);
                }
            } else {
                // If no posisi is selected, no wilayah should be shown
                $wilayah = collect(); // Empty collection
            }

            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Lolos')->get();
            $posisi = Posisi::whereIn('id', $assignedPosisiIds)->get();
            return view('superadmin.lolos.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
           ]);


        }else {

            $posisi = Posisi::all()->sortBy('nama_posisi');
            $wilayah = Wilayah::all()->sortBy('nama_wilayah');
            $query = Kandidat::query();
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $query->where('posisi_id', $request->filter_posisi);
            }
    
            if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                $query->where('wilayah_id', $request->filter_wilayah);
            }
    
            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Lolos')->get();
            return view('superadmin.lolos.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
              
               ]);
        }

     }


     public function tidaklolosindex(Request $request){

        $roleid = auth()->user()->role_id;

    
        if($roleid == 2){

            $userId = auth()->id();
            $detailPosisi = DetailPosisi::where('user_id', $userId)->get();
        
            $assignedPosisi = $detailPosisi->pluck('posisi_id')->unique();
            $assignedWilayah = $detailPosisi->pluck('wilayah_id')->unique();
            
            $assignedPosisiIds = [];
            $assignedWilayahIds = [];
            
            foreach ($assignedPosisi as $posisi) {
                $assignedPosisiIds = array_merge($assignedPosisiIds, explode(',', $posisi));
            }
            
            foreach ($assignedWilayah as $wilayah) {
                $assignedWilayahIds = array_merge($assignedWilayahIds, explode(',', $wilayah));
            }
            
            $assignedPosisiIds = array_unique($assignedPosisiIds);
            $assignedWilayahIds = array_unique($assignedWilayahIds);
            
            // Initialize the query for Kandidat
            $query = Kandidat::query();
        
            // Filtering by position and region based on assigned positions and regions
            $query->where(function ($query) use ($assignedPosisiIds, $assignedWilayahIds) {
                foreach ($assignedPosisiIds as $posisiId) {
                    foreach ($assignedWilayahIds as $wilayahId) {
                        $query->orWhere(function ($query) use ($posisiId, $wilayahId) {
                            $query->where('posisi_id', $posisiId)
                                  ->where('wilayah_id', $wilayahId);
                        });
                    }
                }
            });
              // Filtering by user input
              if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $selectedPosisiId = $request->filter_posisi;
                $query->where('posisi_id', $selectedPosisiId);
        
                // Get assigned wilayah for the selected posisi
                $assignedWilayahIdsForPosisi = $detailPosisi->where('posisi_id', $selectedPosisiId)->pluck('wilayah_id')->toArray();
                $wilayah = Wilayah::whereIn('id', explode(',', implode(',', $assignedWilayahIdsForPosisi)))->get();
                
                // Apply wilayah filter if specified
                if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                    $query->where('wilayah_id', $request->filter_wilayah);
                }
            } else {
                // If no posisi is selected, no wilayah should be shown
                $wilayah = collect(); // Empty collection
            }

            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Tidak Lolos')->get();
            $posisi = Posisi::whereIn('id', $assignedPosisiIds)->get();
            return view('superadmin.tidaklolos.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
           ]);


        }else {

            $posisi = Posisi::all()->sortBy('nama_posisi');
            $wilayah = Wilayah::all()->sortBy('nama_wilayah');
            $query = Kandidat::query();
            if ($request->has('filter_posisi') && $request->filter_posisi != '') {
                $query->where('posisi_id', $request->filter_posisi);
            }
    
            if ($request->has('filter_wilayah') && $request->filter_wilayah != '') {
                $query->where('wilayah_id', $request->filter_wilayah);
            }
    
            $kandidat = $query->orderBy('created_at', 'desc')->where('status_hire','Tidak Lolos')->get();
            return view('superadmin.tidaklolos.index',[
                'kandidat' => $kandidat,
                'posisi' => $posisi,
                'wilayah' => $wilayah,
                'selectedPosisi' => $request->filter_posisi,
                'selectedWilayah' => $request->filter_wilayah,
              
               ]);
        }

     }

     public function saveprocess(Request $request)
     {
      
         $data = $request->all();
         
         $checkedIds = explode(',', $data['checked_ids']);
         
         $status = $data['status'];

         $posisi = $request->posisi_ganti;
         $wilayah = $request->wilayah_ganti;

        
         
        
         foreach ($checkedIds as $id) {            
             $kandidat = Kandidat::find($id);
             $posisiid = $kandidat->posisi_id;

             
             if ($kandidat) {
                $now = now();  // Mengambil waktu saat ini
                $bulan = $now->format('m'); // Mendapatkan bulan
                $tahun = $now->format('Y'); // Mendapatkan tahun
                 $kandidat->status_hire = $status;
                 $kandidat->posisi_id = $posisi;
                 $kandidat->wilayah_id = $wilayah;
                 
                 $kandidat->save();

                 LogTahapan::create([
                    'kandidat_id' => $id,
                    'status_tahapan' => $status,
                    'tanggal' => $now,  // Menyimpan waktu lengkap
                    'bulan' => $bulan,  // Menyimpan bulan
                    'tahun' => $tahun,  // Menyimpan tahun
                    'posisi_id' => $posisi,
                    'wilayah_id' => $wilayah,
                ]);
             }

         }
         
        
         $request->session()->flash('success', 'Status kandidat berhasil diubah.');

         return redirect(route('superadmin.save.index'));
     }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
