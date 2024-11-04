  <!-- ======= Sidebar ======= -->
@php
  // Convert the comma-separated permissions string to an array
  $permissions = explode(',', auth()->user()->role->permision ?? '');
@endphp

  <aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link collapsed" href="{{route('superadmindashboard')}}">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      @if (in_array('MasterData', $permissions))

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Master Data</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>

      <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{route('superadmin.posisi.index')}}">
              <i class="bi bi-circle"></i><span>Posisi</span>
          </a>
        </li>
          
        <li>
          <a href="{{route('superadmin.wilayah.index')}}">
              <i class="bi bi-circle"></i><span>Wilayah</span>
          </a>
        </li>

        <li>
          <a href="{{route('superadmin.sumber.index')}}">
              <i class="bi bi-circle"></i><span>Sumber</span>
          </a>
        </li>

        <li>
            <a href="{{route('superadmin.akunuser.index')}}">
              <i class="bi bi-circle"></i><span>Akun User</span>
            </a>
        </li>

        </ul>
      </li><!-- End Components Nav -->

@endif

@if (in_array('ManajemenPosisi', $permissions))
      <!-- <li class="nav-item">
        <a class="nav-link collapsed" href="">
        <i class="bi-person-lines-fill"></i>
          <span>Manajemen Posisi</span>
        </a>
      </li> -->
@endif
      
@if (in_array('Kandidat', $permissions))
      <li class="nav-item">
        <a class="nav-link collapsed" href="{{route('superadmin.kandidat.index')}}">
        <i class="bi-people-fill"></i>
          <span>Kandidat</span>
        </a>
      </li>
@endif

@php
    
$roleid = auth()->user()->role_id;

if ($roleid == 2) {
            $userId = auth()->id();
            $detailPosisi = \App\Models\DetailPosisi::where('user_id', $userId)->get();
        
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
            $query = \App\Models\Kandidat::query();

       
        
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
            $kandidat = $query->get();
        

            $jumlahBelumProses = $kandidat->where('status_hire', 'Belum Diproses')->count();
    $jumlahPsikotes = $kandidat->where('status_hire', 'Psikotes')->count();
    $jumlahInterviewHR = $kandidat->where('status_hire', 'Interview HR')->count();
    $jumlahInterviewUser = $kandidat->where('status_hire', 'Interview User')->count();
    $jumlahTraining = $kandidat->where('status_hire', 'Training')->count();
    $jumlahTandem = $kandidat->where('status_hire', 'Tandem')->count();
    $jumlahLolos = $kandidat->where('status_hire', 'Lolos')->count();
    $jumlahTidakLolos = $kandidat->where('status_hire', 'Tidak Lolos')->count();
    $jumlahSimpan = $kandidat->where('status_hire', 'Simpan Kandidat')->count();

         } else {

    $jumlahBelumProses = \App\Models\Kandidat::where('status_hire', 'Belum Diproses')->count();
    $jumlahPsikotes = \App\Models\Kandidat::where('status_hire', 'Psikotes')->count();
    $jumlahInterviewHR = \App\Models\Kandidat::where('status_hire', 'Interview HR')->count();
    $jumlahInterviewUser = \App\Models\Kandidat::where('status_hire', 'Interview User')->count();
    $jumlahTraining = \App\Models\Kandidat::where('status_hire', 'Training')->count();
    $jumlahTandem = \App\Models\Kandidat::where('status_hire', 'Tandem')->count();
    $jumlahLolos = \App\Models\Kandidat::where('status_hire', 'Lolos')->count();
    $jumlahTidakLolos = \App\Models\Kandidat::where('status_hire', 'Tidak Lolos')->count();
    $jumlahSimpan = \App\Models\Kandidat::where('status_hire', 'Simpan Kandidat')->count();

  }

@endphp
      
@if (in_array('ProsesRekrutmen', $permissions))
      
<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-bar-chart"></i>
        <span>Proses Rekrutmen</span>
        <i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="{{route('superadmin.belumproses.index')}}">
                <i class="bi bi-circle"></i>
                <span>Belum di Proses</span>
              @if ($jumlahBelumProses > 0)
                <span class="badge bg-danger">{{$jumlahBelumProses}}</span> <!-- Badge untuk Belum di Proses -->
              @endif
            </a>
        </li>

        <li>
            <a href="{{route('superadmin.psikotes.index')}}">
                <i class="bi bi-circle"></i>
                <span>Psikotes</span>
                @if ($jumlahPsikotes > 0)
                <span class="badge bg-danger">{{$jumlahPsikotes}}</span> <!-- Badge untuk Psikotes -->
                @endif
            </a>
        </li>

        <li>
            <a href="{{route('superadmin.itvhr.index')}}">
                <i class="bi bi-circle"></i>
                <span>Interview HR</span>
                @if ($jumlahInterviewHR > 0)
                <span class="badge bg-danger">{{$jumlahInterviewHR}}</span> <!-- Badge untuk Interview HR -->
                @endif
            </a>
        </li>

         <li>
            <a href="{{route('superadmin.itvuser.index')}}">
                <i class="bi bi-circle"></i>
                <span>Interview User</span>
                @if ($jumlahInterviewUser > 0)
                <span class="badge bg-danger">{{$jumlahInterviewUser}}</span> <!-- Badge untuk Interview User -->
                @endif
            </a>
        </li>

        <li>
            <a href="{{route('superadmin.training.index')}}">
                <i class="bi bi-circle"></i>
                <span>Training</span>
                @if ($jumlahTraining > 0)
                <span class="badge bg-danger">{{$jumlahTraining}}</span> <!-- Badge untuk Training -->
                @endif
            </a>
        </li>

        <li>
            <a href="{{route('superadmin.tandem.index')}}">
                <i class="bi bi-circle"></i>
                <span>Tandem</span>
                @if ($jumlahTandem > 0)
                <span class="badge bg-danger">{{$jumlahTandem}}</span> <!-- Badge untuk Tandem -->
                @endif
            </a>
        </li>

        <li>
            <a href="{{route('superadmin.lolos.index')}}">
                <i class="bi bi-circle"></i>
                <span>Lolos</span>
                @if ($jumlahLolos > 0)
                <span class="badge bg-danger">{{$jumlahLolos}}</span> <!-- Badge untuk Lolos -->
                @endif
            </a>
        </li>

        <li>
            <a href="{{route('superadmin.tidaklolos.index')}}">
                <i class="bi bi-circle"></i>
                <span>Tidak Lolos</span>
                @if ($jumlahTidakLolos > 0)
                <span class="badge bg-danger">{{$jumlahTidakLolos}}</span> <!-- Badge untuk Tidak Lolos -->
                @endif
            </a>
        </li>

        <li>
            <a href="{{route('superadmin.save.index')}}">
                <i class="bi bi-circle"></i>
                <span>Simpan Kandidat</span>
                @if ($jumlahSimpan > 0)
                <span class="badge bg-danger">{{$jumlahSimpan}}</span> <!-- Badge untuk Simpan -->
                @endif
            </a>
        </li>

    </ul>
</li>
@endif

<!-- @if (in_array('LogActivity', $permissions))
      <li class="nav-item">
        <a class="nav-link collapsed" href="{{route('superadmin.logactivity.index')}}">
        <i class="bi-graph-up"></i>
          <span>Log Aktivitas</span>
        </a>
      </li>
@endif -->

<li class="nav-item">
  <a class="nav-link collapsed" href="{{route('superadmin.masterkonfirm.index')}}">
    <i class="bi bi-check-circle-fill"></i>
    <span>Data Konfirm Pemanggilan</span>
  </a>
</li>

@if (in_array('LaporanPerformance', $permissions))
<li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#icons-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-journal-text"></i><span>Laporan Performance</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="icons-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">

        <li>
            <a href="{{route(name: 'superadmin.targetjumlah.index')}}">
              <i class="bi bi-circle"></i><span>Target MPP & Jumlah Mitra Existing</span>
            </a>
        </li>
          <li>
            <a href="{{route(name: 'superadmin.masteraktif.index')}}">
              <i class="bi bi-circle"></i><span>Karyawan Aktif</span>
            </a>
          </li>
          <li>
            <a href="{{route(name: 'superadmin.mastertidakaktif.index')}}">
              <i class="bi bi-circle"></i><span>Karyawan Tidak Aktif</span>
            </a>
          </li>
          <li>
            <a href="{{route(name: 'superadmin.mastertrainingtandem.index')}}">
              <i class="bi bi-circle"></i><span>Karyawan Training & Tandem</span>
            </a>
          </li>
          <li>
            <a href="{{route(name: 'superadmin.laporanperformance.index')}}">
              <i class="bi bi-circle"></i><span>Laporan Performance</span>
            </a>
          </li>
       
        </ul>
      </li><!-- End Icons Nav -->
@endif


<!-- <li class="nav-item">
  <a class="nav-link collapsed" href="{{route(name: 'superadmin.masteraktif.index')}}">
    <i class="bi bi-person-check-fill"></i>
      <span>Karyawan Aktif</span>
  </a>
</li>


<li class="nav-item">
  <a class="nav-link collapsed" href="{{route(name: 'superadmin.mastertidakaktif.index')}}">
  <i class="bi bi-person-fill-dash"></i>
      <span>Karyawan Tidak Aktif</span>
  </a>
</li>


<li class="nav-item">
  <a class="nav-link collapsed" href="{{route(name: 'superadmin.mastertrainingtandem.index')}}">
  <i class="bi bi-person-gear"></i>
      <span>Karyawan Training & Tandem</span>
  </a>
</li> -->

  </ul>
</aside><!-- End Sidebar-->

