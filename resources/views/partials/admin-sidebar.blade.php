<div class="sidebar" id="adminSidebar">
    <!-- Sidebar Brand with Logo -->
    <div class="sidebar-brand">
        @php
            $settings = \App\Models\SettingSystem::instance()->toSettingsArray();
            $schoolLogo = $settings['school_logo'] ?? null;
        @endphp
        
        @if($schoolLogo)
            <div class="sidebar-brand-logo">
                <img src="{{ asset('storage/' . $schoolLogo) }}" alt="Logo Sekolah">
            </div>
        @else
            <div class="sidebar-brand-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
        @endif
        
        <div class="sidebar-brand-text">
            SPMB
        </div>
        
        <!-- Toggle Button in Sidebar -->
        <button class="sidebar-toggle-btn d-none d-lg-flex" type="button" id="sidebarToggle" title="Toggle Sidebar">
            <i class="fas fa-circle"></i>
        </button>
    </div>

    <!-- Sidebar Navigation -->
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
                <i class="fas fa-home"></i> <span class="nav-text">Dashboard</span>
            </a>
        </li>
        
        @if(auth()->check() && (auth()->user()->isAdministrator() || auth()->user()->isPanitia()))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pendaftar.index') || request()->routeIs('pendaftar.create') || request()->routeIs('pendaftar.edit') || request()->routeIs('pendaftar.show') ? 'active' : '' }}" href="{{ route('pendaftar.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Data Pendaftar">
                <i class="fas fa-users"></i> <span class="nav-text">Data Pendaftar</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pendaftar.verification-index') || request()->routeIs('pendaftar.daftar-ulang') ? 'active' : '' }}" href="{{ route('pendaftar.verification-index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Verifikasi Daftar Ulang">
                <i class="fas fa-money-bill"></i> <span class="nav-text">Verifikasi Daftar Ulang</span>
            </a>
        </li>
        @endif
        
        @if(auth()->check() && auth()->user()->isAdministrator())
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('pendaftar.trashed') ? 'active' : '' }}" href="{{ route('pendaftar.trashed') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Data Terhapus">
                <i class="fas fa-trash-restore"></i> <span class="nav-text">Data Terhapus</span>
            </a>
        </li>
        @endif
        
        @if(auth()->check() && (auth()->user()->isAdministrator() || auth()->user()->isPanitia()))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('report.*') ? 'active' : '' }}" href="{{ route('report.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Laporan & Cetak">
                <i class="fas fa-file-pdf"></i> <span class="nav-text">Laporan & Cetak</span>
            </a>
        </li>
        <li class="nav-item has-submenu">
            <a class="nav-link {{ request()->routeIs('jaringan.*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#jaringanSubmenu" aria-expanded="{{ request()->routeIs('jaringan.*') ? 'true' : 'false' }}" title="Kelola Jaringan">
                <i class="fas fa-code-branch"></i> 
                <span class="nav-text">Kelola Jaringan</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="submenu collapse {{ request()->routeIs('jaringan.*') ? 'show' : '' }}" id="jaringanSubmenu">
                <li>
                    <a class="submenu-link {{ request()->routeIs('jaringan.merge') ? 'active' : '' }}" href="{{ route('jaringan.merge') }}">
                        <i class="fas fa-layer-group"></i>
                        <span class="nav-text">Merge Full</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('jaringan.merge-selective') ? 'active' : '' }}" href="{{ route('jaringan.merge-selective') }}">
                        <i class="fas fa-check-square"></i>
                        <span class="nav-text">Merge Selective</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('jaringan.history') ? 'active' : '' }}" href="{{ route('jaringan.history') }}">
                        <i class="fas fa-history"></i>
                        <span class="nav-text">History Merge</span>
                    </a>
                </li>
            </ul>
        </li>
        @endif
        
        @if(auth()->check() && auth()->user()->canAccessWhatsApp())
        <li class="nav-item has-submenu">
            <a class="nav-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#whatsappSubmenu" aria-expanded="{{ request()->routeIs('whatsapp.*') ? 'true' : 'false' }}" title="WhatsApp Gateway">
                <i class="fab fa-whatsapp"></i> 
                <span class="nav-text">WhatsApp Gateway</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="submenu collapse {{ request()->routeIs('whatsapp.*') ? 'show' : '' }}" id="whatsappSubmenu">
                <li>
                    <a class="submenu-link {{ request()->routeIs('whatsapp.index') ? 'active' : '' }}" href="{{ route('whatsapp.index') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('whatsapp.send') ? 'active' : '' }}" href="{{ route('whatsapp.send') }}">
                        <i class="fas fa-paper-plane"></i>
                        <span class="nav-text">Kirim Pesan</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('whatsapp.phone-list') ? 'active' : '' }}" href="{{ route('whatsapp.phone-list') }}">
                        <i class="fas fa-address-book"></i>
                        <span class="nav-text">Rekap Nomor HP</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('whatsapp.broadcast') ? 'active' : '' }}" href="{{ route('whatsapp.broadcast') }}">
                        <i class="fas fa-bullhorn"></i>
                        <span class="nav-text">Broadcast</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('whatsapp.logs') ? 'active' : '' }}" href="{{ route('whatsapp.logs') }}">
                        <i class="fas fa-history"></i>
                        <span class="nav-text">Log Pesan</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('whatsapp.templates*') ? 'active' : '' }}" href="{{ route('whatsapp.templates') }}">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">Template Pesan</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('whatsapp.settings') ? 'active' : '' }}" href="{{ route('whatsapp.settings') }}">
                        <i class="fas fa-cog"></i>
                        <span class="nav-text">Pengaturan</span>
                    </a>
                </li>
            </ul>
        </li>
        @endif
        
        @if(auth()->check() && auth()->user()->isAdministrator())
        <li class="nav-item has-submenu">
            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#settingsSubmenu" aria-expanded="{{ request()->routeIs('settings.*') ? 'true' : 'false' }}" title="Pengaturan Sistem">
                <i class="fas fa-cog"></i> 
                <span class="nav-text">Pengaturan Sistem</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="submenu collapse {{ request()->routeIs('settings.*') ? 'show' : '' }}" id="settingsSubmenu">
                <li>
                    <a class="submenu-link {{ request()->routeIs('settings.index') && request()->input('tab') == 'profil' ? 'active' : '' }}" href="{{ route('settings.index') }}?tab=profil">
                        <i class="fas fa-school"></i>
                        <span class="nav-text">Profil Sekolah</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('settings.index') && request()->input('tab') == 'pendaftaran' ? 'active' : '' }}" href="{{ route('settings.index') }}?tab=pendaftaran">
                        <i class="fas fa-user-plus"></i>
                        <span class="nav-text">Pendaftaran</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('settings.index') && request()->input('tab') == 'branding' ? 'active' : '' }}" href="{{ route('settings.index') }}?tab=branding">
                        <i class="fas fa-palette"></i>
                        <span class="nav-text">Branding</span>
                    </a>
                </li>
                <li>
                    <a class="submenu-link {{ request()->routeIs('settings.index') && request()->input('tab') == 'dokumen' ? 'active' : '' }}" href="{{ route('settings.index') }}?tab=dokumen">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">Dokumen</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Manajemen User">
                <i class="fas fa-user-shield"></i> <span class="nav-text">Manajemen User</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.tahun-ajaran.*') ? 'active' : '' }}" href="{{ route('admin.tahun-ajaran.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Tahun Pelajaran">
                <i class="fas fa-calendar-alt"></i> <span class="nav-text">Tahun Pelajaran</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('gateway.*') ? 'active' : '' }}" href="{{ route('gateway.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Gateway Management">
                <i class="fas fa-server"></i> <span class="nav-text">Gateway Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Backup & Restore">
                <i class="fas fa-database"></i> <span class="nav-text">Backup & Restore</span>
            </a>
        </li>
        @endif
        
    </ul>
</div>
