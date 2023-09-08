<!--begin::Header-->
<div id="kt_header" class="header" data-kt-sticky="true" data-kt-sticky-name="header"
    data-kt-sticky-offset="{default: '200px', lg: '300px'}">
    <!--begin::Container-->
    <div class="container-xxl d-flex flex-grow-1 flex-stack">
        <!--begin::Header Logo-->
        <div class="d-flex align-items-center me-5">
            <!--begin::Heaeder menu toggle-->
            <div class="d-lg-none btn btn-icon btn-active-color-primary w-30px h-30px ms-n2 me-3"
                id="kt_header_menu_toggle">
                <i class="ki-duotone ki-abstract-14 fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
            <!--end::Heaeder menu toggle-->
            <a href="/dashboard">
                <img alt="Logo" src="{{ URL::asset('assets/media/logos/demo11.svg') }}"
                    class="theme-light-show h-20px h-lg-30px" />
                <img alt="Logo" src="{{ URL::asset('assets/media/logos/demo11-dark.svg') }}"
                    class="theme-dark-show h-20px h-lg-30px" />
            </a>
        </div>
        <!--end::Header Logo-->
        <!--begin::Topbar-->
        <div class="flex-shrink-0 d-flex align-items-center">
            <!--begin::Theme mode-->
            <div class="d-flex align-items-center ms-3 ms-lg-4">
                <!--begin::Menu toggle-->
                <a href="#"
                    class="btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline btn-active-bg-light w-30px h-30px w-lg-40px h-lg-40px"
                    data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-night-day theme-light-show fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                        <span class="path7"></span>
                        <span class="path8"></span>
                        <span class="path9"></span>
                        <span class="path10"></span>
                    </i>
                    <i class="ki-duotone ki-moon theme-dark-show fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </a>
                <!--begin::Menu toggle-->
                <!--begin::Menu-->
                <div class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold fs-base w-150px"
                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                    <!--begin::Menu item-->
                    <div class="px-3 my-0 menu-item">
                        <a href="#" class="px-3 py-2 menu-link" data-kt-element="mode" data-kt-value="light">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-duotone ki-night-day fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                    <span class="path6"></span>
                                    <span class="path7"></span>
                                    <span class="path8"></span>
                                    <span class="path9"></span>
                                    <span class="path10"></span>
                                </i>
                            </span>
                            <span class="menu-title">Light</span>
                        </a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="px-3 my-0 menu-item">
                        <a href="#" class="px-3 py-2 menu-link" data-kt-element="mode" data-kt-value="dark">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-duotone ki-moon fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Dark</span>
                        </a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="px-3 my-0 menu-item">
                        <a href="#" class="px-3 py-2 menu-link" data-kt-element="mode" data-kt-value="system">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-duotone ki-screen fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">System</span>
                        </a>
                    </div>
                    <!--end::Menu item-->
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Theme mode-->
            <!--begin::User-->
            <div class="d-flex align-items-center ms-3 ms-lg-4" id="kt_header_user_menu_toggle">
                <!--begin::Menu- wrapper-->
                <!--begin::User icon(remove this button to use user avatar as menu toggle)-->
                <div class="btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline btn-active-bg-light w-30px h-30px w-lg-40px h-lg-40px"
                    data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-user fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::User icon-->
                <!--begin::User account menu-->
                <div class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold fs-6 w-275px"
                    data-kt-menu="true">
                    <!--begin::Menu item-->
                    <div class="px-3 menu-item">
                        <div class="px-3 menu-content d-flex align-items-center">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-50px me-5">
                                <img alt="Logo" src="{{ URL::asset('assets/media/avatars/300-1.jpg') }}" />
                            </div>
                            <!--end::Avatar-->
                            <!--begin::Username-->
                            <div class="d-flex flex-column">
                                <div class="fw-bold d-flex align-items-center fs-5">{{ auth()->user()->name }}
                                    <span
                                        class="px-2 py-1 badge badge-light-success fw-bold fs-8 ms-2">{{ auth()->user()->role }}</span>
                                </div>
                                <a href="#"
                                    class="fw-semibold text-muted text-hover-primary fs-7">{{ auth()->user()->email }}</a>
                            </div>
                            <!--end::Username-->
                        </div>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu separator-->
                    <div class="my-2 separator"></div>
                    <!--end::Menu separator-->
                    <!--begin::Menu item-->
                    <div class="px-5 my-1 menu-item">
                        <a href="#" class="px-5 menu-link">Account Settings</a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="px-5 menu-item">
                        <a href="{{ route('logout') }}" class="px-5 menu-link"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Keluar</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                    <!--end::Menu item-->
                </div>
                <!--end::User account menu-->
                <!--end::Menu wrapper-->
            </div>
            <!--end::User -->
            <!--begin::Sidebar Toggler-->
            <!--end::Sidebar Toggler-->
        </div>
        <!--end::Topbar-->
    </div>
    <!--end::Container-->
    <!--begin::Separator-->
    <div class="separator"></div>
    <!--end::Separator-->
    <!--begin::Container-->
    <div class="header-menu-container container-xxl d-flex flex-stack h-lg-75px w-100" id="kt_header_nav">
        <!--begin::Menu wrapper-->
        <div class="header-menu flex-column flex-lg-row" data-kt-drawer="true" data-kt-drawer-name="header-menu"
            data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
            data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
            data-kt-drawer-toggle="#kt_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
            <!--begin::Menu-->
            <div class="px-2 my-5 menu menu-rounded menu-column menu-lg-row menu-root-here-bg-desktop menu-active-bg menu-state-primary menu-title-gray-800 menu-arrow-gray-400 align-items-stretch flex-grow-1 my-lg-0 px-lg-0 fw-semibold fs-6"
                id="#kt_header_menu" data-kt-menu="true">
                <!--begin:Menu item-->
                <div class="menu-item me-0 me-lg-2 {{ request()->routeIs('dashboard') ? ' here' : '' }}">
                    <!--begin:Menu link-->
                    <a href="{{ route('dashboard') }}" class="py-3 menu-link">
                        <span class="menu-title">Dashboards</span>
                        <span class="menu-arrow d-lg-none"></span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
                @can('simpan cabang', 'baca cabang', 'update cabang', 'hapus cabang')
                    <!--begin:Menu item-->
                    <div class="menu-item me-0 me-lg-2 {{ request()->routeIs('cabang.*') ? ' here' : '' }}">
                        <!--begin:Menu link-->
                        <a href="{{ route('cabang.index') }}" class="py-3 menu-link">
                            <span class="menu-title">Cabang</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                @endcan
                @can('simpan supplier', 'baca supplier', 'update supplier', 'hapus supplier')
                    <!--begin:Menu item-->
                    <div class="menu-item me-0 me-lg-2 {{ request()->routeIs('supplier.*') ? ' here' : '' }}">
                        <!--begin:Menu link-->
                        <a href="{{ route('supplier.index') }}" class="py-3 menu-link">
                            <span class="menu-title">Supplier</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </a>
                    </div>
                    <!--end:Menu link-->
                @endcan
                @can('simpan customer', 'baca customer', 'update customer', 'hapus customer')
                    <div class="menu-item me-0 me-lg-2 {{ request()->routeIs('customer.*') ? ' here' : '' }}">
                        <!--begin:Menu link-->
                        <a href="{{ route('customer.index') }}" class="py-3 menu-link">
                            <span class="menu-title">Customer</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                @endcan
                <!--end:Menu item-->
                @can('simpan produk', 'baca produk', 'update produk', 'hapus produk')
                    <!--begin:Menu item-->
                    <div class="menu-item me-0 me-lg-2 {{ request()->routeIs('produk.*') ? ' here' : '' }}">
                        <!--begin:Menu link-->
                        <a href="{{ route('produk.index') }}" class="py-3 menu-link">
                            <span class="menu-title">Produk</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                @endcan
                @role('admin|kasir')
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
                        class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention me-0 me-lg-2">
                        <!--begin:Menu link-->
                        <span class="py-3 menu-link">
                            <span class="menu-title">Pembelian</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown px-lg-2 py-lg-4 w-lg-200px">
                            <!--begin:Menu item-->
                            <div class="menu-item {{ request()->routeIs('pembelian.*') ? ' here' : '' }}">
                                <!--begin:Menu link-->
                                <a class="menu-link"href="{{ route('pembelian.index') }}">
                                    <span class="menu-title">Data Pembelian</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item {{ request()->routeIs('pembelian-retur.*') ? ' here' : '' }}">
                                <!--begin:Menu link-->
                                <a class="menu-link"href="{{ route('pembelian-retur.index') }}">
                                    <span class="menu-title">Data Retur</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item {{ request()->routeIs('hutang') ? ' here' : '' }}">
                                <!--begin:Menu link-->
                                <a class="menu-link"href="{{ route('hutang') }}">
                                    <span class="menu-title">Data Hutang</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                @endrole
                @can('simpan penjualan', 'baca penjualan', 'update penjualan', 'hapus penjualan')
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
                        class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention me-0 me-lg-2">
                        <!--begin:Menu link-->
                        <span class="py-3 menu-link">
                            <span class="menu-title">Penjualan</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown px-lg-2 py-lg-4 w-lg-200px">
                            <!--begin:Menu item-->
                            <div class="menu-item {{ request()->routeIs('penjualan.*') ? ' here' : '' }}">
                                <!--begin:Menu link-->
                                <a class="menu-link"href="{{ route('penjualan.index') }}">
                                    <span class="menu-title">Data Penjualan</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item {{ request()->routeIs('penjualan-retur.*') ? ' here' : '' }}">
                                <!--begin:Menu link-->
                                <a class="menu-link"href="{{ route('penjualan-retur.index') }}">
                                    <span class="menu-title">Data Retur</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item {{ request()->routeIs('piutang') ? ' here' : '' }}">
                                <!--begin:Menu link-->
                                <a class="menu-link"href="{{ route('piutang') }}">
                                    <span class="menu-title">Data Piutang</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                @endcan
                @can('simpan inventory', 'baca inventory', 'update inventory', 'hapus inventory')
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
                        class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention me-0 me-lg-2">
                        <!--begin:Menu link-->
                        <span class="py-3 menu-link">
                            <span class="menu-title">Inventory</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown px-lg-2 py-lg-4 w-lg-200px">
                            <!--begin:Menu item-->
                            <div class="menu-item {{ request()->routeIs('inventori.*') ? ' here' : '' }}">
                                <!--begin:Menu link-->
                                <a class="menu-link"href="{{ route('inventori.index') }}">
                                    <span class="menu-title">Data Inventory</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item {{ request()->routeIs('pindah-stok.*') ? ' here' : '' }}">
                                <!--begin:Menu link-->
                                <a class="menu-link"href="{{ route('pindah-stok.index') }}">
                                    <span class="menu-title">Pindah Stok</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                @endcan
                @can('baca kas', 'simpan kas', 'hapus kas', 'update kas')
                    <!--begin:Menu item-->
                    <div class="menu-item me-0 me-lg-2 {{ request()->routeIs('kas.*') ? ' here' : '' }}">
                        <!--begin:Menu link-->
                        <a href="{{ route('kas.index') }}" class="py-3 menu-link">
                            <span class="menu-title">Kas</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                @endcan
                <!--begin:Menu item-->
                    <div class="menu-item me-0 me-lg-2 {{ request()->routeIs('mutasi-kas.*') ? ' here' : '' }}">
                        <!--begin:Menu link-->
                        <a href="{{ route('mutasi-kas.index') }}" class="py-3 menu-link">
                            <span class="menu-title">Mutasi Kas</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                <!--end:Menu item-->
                <!--begin:Menu item-->
                    <div class="menu-item me-0 me-lg-2 {{ request()->routeIs('settlement.*') ? ' here' : '' }}">
                        <!--begin:Menu link-->
                        <a href="{{ route('settlement.index') }}" class="py-3 menu-link">
                            <span class="menu-title">Settlement</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                <!--end:Menu item-->
                @can('baca laporan')
                    <div class="menu-item me-0 me-lg-2">
                        <!--begin:Menu link-->
                        <span class="py-3 menu-link">
                            <span class="menu-title">Laporan</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                    </div>
                @endcan
                @role('admin')
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention me-0 me-lg-2">
                    <!--begin:Menu link-->
                    <span class="py-3 menu-link">
                        <span class="menu-title">Pengaturan</span>
                        <span class="menu-arrow d-lg-none"></span>
                    </span>
                    <!--end:Menu link-->
                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown px-lg-2 py-lg-4 w-lg-200px">
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link"href="{{ route('user.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-people fs-2">
                                        <i class="path1"></i>
                                        <i class="path2"></i>
                                        <i class="path3"></i>
                                        <i class="path4"></i>
                                        <i class="path5"></i>
                                    </i>
                                </span>
                                <span class="menu-title">Data User</span>
                            </a>
                            <!--end:Menu link-->
                            <!--begin:Menu link-->
                            <a class="menu-link"href="{{ route('permission.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-lock-2 fs-2">
                                        <i class="path1"></i>
                                        <i class="path2"></i>
                                        <i class="path3"></i>
                                        <i class="path4"></i>
                                        <i class="path5"></i>
                                    </i>
                                </span>
                                <span class="menu-title">Data Permission</span>
                            </a>
                            <!--end:Menu link-->
                            <!--begin:Menu link-->
                            <a class="menu-link"href="{{ route('role-permission.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-lock-2 fs-2">
                                        <i class="path1"></i>
                                        <i class="path2"></i>
                                        <i class="path3"></i>
                                        <i class="path4"></i>
                                        <i class="path5"></i>
                                    </i>
                                </span>
                                <span class="menu-title">Role & Permission</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->
                    </div>
                    <!--end:Menu sub-->
                </div>
                @endrole
            </div>
            <!--end::Menu-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::Container-->
</div>
<!--end::Header-->
