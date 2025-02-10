<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="{{ asset('img/logo.png') }}" width="3" alt="logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">{{ __('aside.storeName') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('img/logo.png') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            @if (Auth()->user()->type != 4)
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">

                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home fa-spin text-orange"></i>
                            <p>
                                الرئيسية
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('calibers.index') }}"
                            class="nav-link {{ request()->is('calibers*') ? 'active' : '' }}">
                            <i class="fas fa-database fa-spin text-success"></i>
                            <p>
                                {{ __('aside.categoryName') }}
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('types.index') }}"
                            class="nav-link {{ request()->is('types*') ? 'active' : '' }}">
                            <i class="fas fa-ring fa-spin text-danger"></i>
                            <p>
                                {{ __('aside.types') }}
                            </p>
                        </a>
                    </li>
                        <nav class="mt-2">
                            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                                data-accordion="false">

                                <li class="nav-item menu-open">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon fas fa-box"></i>
                                        <p>
                                            إدارة الصندوق
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('box.index') }}"
                                                class="nav-link {{ request()->is('box*') ? 'active' : '' }}">
                                                <i class="fas fa-box fa-spin text-secondary"></i>
                                                <p>
                                                    فتح الصندوق
                                                </p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('costs_types.index') }}"
                                                class="nav-link {{ request()->is('cost_types*') ? 'active' : '' }}">
                                                <i class="fas fa-receipt fa-spin text-green"></i>
                                                <p>
                                                    أنواع المصاريف
                                                </p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{  route('costs.index')  }}"
                                                class="nav-link {{ request()->is('costs*') ? 'active' : '' }}">
                                                <i class="fas fa-cash-register fa-spin text-orange"></i>
                                                <p>
                                                    المصاريف
                                                </p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                            data-accordion="false">

                            <li class="nav-item menu-open">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-tachometer-alt"></i>
                                    <p>
                                        إدارة المنتجات
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('products.index') }}"
                                            class="nav-link {{ request()->is('products*') ? 'active' : '' }}">
                                            <i class="fas fa-tags fa-spin text-yellow"></i>
                                            <p>
                                                {{ __('aside.products') }}
                                            </p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ route('quantities.index') }}"
                                            class="nav-link {{ request()->is('quantities*') ? 'active' : '' }}">
                                            <i class="fas fa-box fa-spin text-red"></i>
                                            <p>
                                                الكميات
                                            </p>
                                        </a>
                                    </li>


                                </ul>
                            </li>
                        </ul>
                    </nav>
                    @if (auth()->user()->type == 1)
                        <li class="nav-item">
                            <a href="{{ route('invantory.index') }}"
                                class="nav-link {{ request()->is('invantory*') ? 'active' : '' }}">
                                <i class="fas fa-warehouse fa-spin text-blue "></i>
                                <p>
                                    الجرد
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('users.index') }}"
                                class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                                <i class="fas fa-users fa-spin text-warning"></i>
                                <p>
                                    الموظّفون
                                </p>
                            </a>
                        </li> 
                        <li class="nav-item">
                            <a href="{{ route('suppliers.index') }}"
                                class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}">
                                <i class="fas fa-industry fa-spin text-red"></i>
                                <p>
                                    المورّدون
                                </p>
                            </a>
                        </li> 
                    @endif

                </ul>
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        <li class="nav-item menu-open">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    تقارير
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('reports.index') }}" class="nav-link">
                                        <i class="fas fa-wallet text-green  fa-spin nav-icon "></i>
                                        <p>كشوف</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('caliber_trans') }}" class="nav-link">
                                        <i class="fas fa-sync text-blue  fa-spin nav-icon "></i>
                                        <p>تحويلات</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('display_saved_invantory') }}" class="nav-link">
                                        <i class="fas fa-archive fa-spin nav-icon text-success"></i>
                                        <p>المجرودات</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            @endif 
            @if (Auth()->user()->type == 4)
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">

                    <li class="nav-item">
                        <a href="{{ route('dataentry.index') }}"
                            class="nav-link {{ request()->is('dataentry') ? 'active' : '' }}">
                            <i class="fas fa-home fa-spin text-orange"></i>
                            <p>
                                الرئيسية
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('dataentry.getproducts') }}"
                            class="nav-link {{ request()->is('dataentry/products/create') ? 'active' : '' }}">
                            <i class="fas fa-tags fa-spin text-success"></i>
                            <p>
                                المنتجات
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('dataentry.create_from_quantity') }}"
                            class="nav-link {{ request()->is('dataentry/products/fromquantity*') ? 'active' : '' }}">
                            <i class="fas fa-plus fa-spin text-blue"></i>
                            <p>
                                 منتج من كمية
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('quantities.entry.index') }}"
                            class="nav-link {{ request()->is('quantities*') ? 'active' : '' }}">
                            <i class="fas fa-box fa-spin text-red"></i>
                            <p>
                                الكميات
                            </p>
                        </a>
                    </li>
                </ul>
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        
                        <li class="nav-item menu-open">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-box"></i>
                                <p>
                                    إدارة الصندوق
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('box.entry.index') }}"
                                        class="nav-link {{ request()->is('box*') ? 'active' : '' }}">
                                        <i class="fas fa-box fa-spin text-secondary"></i>
                                        <p>
                                            فتح الصندوق
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('costs_types.entry.index') }}"
                                        class="nav-link {{ request()->is('cost_types*') ? 'active' : '' }}">
                                        <i class="fas fa-receipt fa-spin text-green"></i>
                                        <p>
                                            أنواع المصاريف
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{  route('costs.entry.index')  }}"
                                        class="nav-link {{ request()->is('costs*') ? 'active' : '' }}">
                                        <i class="fas fa-cash-register fa-spin text-orange"></i>
                                        <p>
                                            المصاريف
                                        </p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            @endif
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
