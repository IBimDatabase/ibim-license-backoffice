<!DOCTYPE html>
<html lang="en" ng-app="licenseManagement">

<head>
    @include('layouts.templates.header') 
</head>

<body class="g-sidenav-show bg-gray-100">
    {{-- @auth --}}
    @if(!Route::is('login'))
        @include('layouts.navbars.sidebar')
        @include('layouts.navbars.nav')
    @endif
    {{-- @endauth --}}
        
    @yield('content')

    {{-- @auth --}}
    @if(!Route::is('login'))
        @include('layouts.templates.footer')
    @endif
    {{-- @endauth --}}
    @include('layouts.templates.footer-scripts')
</body>

</html>
