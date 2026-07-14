@php
    use App\Models\Setting;
    $setting = Setting::first();
@endphp

<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">

    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>

        <div>
            <a class="navbar-brand brand-logo" href="{{ route('admin.home') }}">

                <img 
                    src="{{ $setting && $setting->logo 
                        ? asset($setting->logo) 
                        : asset('admin/assets/images/logo.svg') }}"
                    class="side-logo"
                    alt="logo" />
            </a>
        </div>
    </div>

    <div class="navbar-menu-wrapper d-flex align-items-top">

        {{-- Greeting --}}
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h1 class="welcome-text">

                    @php
                        $hour = now()->format('H');
                    @endphp

                    @if ($hour < 12)
                        Good Morning
                    @elseif ($hour < 17)
                        Good Afternoon
                    @else
                        Good Evening
                    @endif

                    <span class="text-black fw-bold">
                        {{ auth()->user()->name }}
                    </span>
                </h1>
            </li>
        </ul>

        {{-- Right Menu --}}
        <ul class="navbar-nav ms-auto">

            {{-- Notifications --}}
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="icon-bell"></i>
                    <span class="count">2</span>
                </a>

                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                     id="notification-list">

                    <div id="notification-scroll" class="overflow-auto">
                        <!-- Notifications -->
                    </div>
                </div>
            </li>

            {{-- User --}}
            <li class="nav-item dropdown d-none d-lg-block user-dropdown">

                <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown">
                    <img class="img-xs rounded-circle"
                         src="{{ asset('admin/assets/images/faces/face8.png') }}"
                         alt="Profile image">
                </a>

                <div class="dropdown-menu dropdown-menu-right navbar-dropdown">

                    <div class="dropdown-header text-center">

                        <img class="img-md rounded-circle"
                             src="{{ asset('admin/assets/images/faces/face8.png') }}"
                             style="height:40px; width:40px;"
                             alt="Profile image">

                        <p class="mb-1 mt-3 fw-semibold">{{ auth()->user()->name }}</p>
                        <p class="fw-light text-muted mb-0">{{ auth()->user()->email }}</p>
                    </div>

                    <a href="{{ route('admin.profile') }}" class="dropdown-item">
                        <i class="mdi mdi-account-outline me-2 text-primary"></i> My Profile
                    </a>

                    <a href="{{ route('admin.logout') }}" class="dropdown-item">
                        <i class="mdi mdi-power me-2 text-primary"></i> Sign Out
                    </a>
                </div>
            </li>

            {{-- Small Logo --}}
            <li class="nav-item dropdown">
                <a class="nav-link count-indicator" href="#">
                    <img 
                        src="{{ $setting && $setting->logo 
                            ? asset($setting->logo) 
                            : asset('admin/assets/images/logo.svg') }}"
                        alt="logo"
                        style="height:30px">
                </a>
            </li>

        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center"
                type="button"
                data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>

    </div>
</nav>
