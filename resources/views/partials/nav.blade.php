<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="index3.html" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button class="btn btn-warning" type="submit" class="nav-link">تسجيل خروج</button>
            </form>
        </li>
    </ul>
    <!-- SEARCH FORM -->
    <form class="form-inline ml-3">
        <div class="input-group input-group-sm">
            <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Right navbar links -->
    <ul class="navbar-nav mr-auto-navbav">
        <!-- Messages Dropdown Menu -->
        <li class="nav-item dropdown">
                @if (session()->get('locale') == 'ar')
                    <a class="nav-link" href="{{ route('set-locale', 'gr') }}">
                        <i><img src="{{ asset('img/german.png') }}" alt="arabic" class="img-size-50"></i>
                    </a>
                @else
                    <a class="nav-link" href="{{ route('set-locale', 'ar') }}">
                        <i><img src="{{ asset('img/ar.png') }}" alt="german" class="img-size-50"></i>
                    </a>
                @endif
        </li>
    </ul>
</nav>
