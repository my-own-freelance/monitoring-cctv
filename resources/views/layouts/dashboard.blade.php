@php
    $color = \App\Models\CustomTemplate::find(1);
    $logoColor = $color && $color->logo_header_color ? $color->logo_header_color : 'blue';
    $topbarColor = $color && $color->topbar_color ? $color->topbar_color : 'blue2';
    $sidebarColor = $color && $color->sidebar_color ? $color->sidebar_color : 'white';
    $bgColor = $color && $color->bd_color ? $color->bd_color : 'bg1';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    {{-- <link rel="icon" href="{{ asset('dashboard/icon/icon.png') }}" type="image/x-icon" /> --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    @include('partials.dashboard.styles')
    <title>@yield('title')</title>
    @stack('styles')
</head>

<body data-background-color="{{ $bgColor }}">
    <div class="wrapper">
        <div class="main-header">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="{{ $logoColor }}">
                <a href="{{ route('dashboard') }}" class="logo">
                    <h1 class="text-white mt-3" style="font-weight: 800!important">CCTV</h1>
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
                    data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="icon-menu"></i>
                    </span>
                </button>
                <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar">
                        <i class="icon-menu"></i>
                    </button>
                </div>
            </div>
            <!-- End Logo Header -->

            {{-- navbar header --}}
            @include('partials.dashboard.navbar')
            {{-- end navbar header --}}
        </div>

        @include('partials.dashboard.sidebar')

        <div class="main-panel">
            <div class="container">
                <div class="page-inner mt-5">
                    @yield('content')
                </div>
            </div>
            @include('partials.dashboard.footer')
        </div>
        @include('partials.dashboard.custom-template')
    </div>
    @include('partials.dashboard.scripts')
    @stack('scripts')
</body>

</html>
