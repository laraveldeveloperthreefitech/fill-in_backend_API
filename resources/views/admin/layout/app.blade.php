<!DOCTYPE html>
<html lang="en">
  @include('admin.layout.head')
  <body class="with-welcome-text">
    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
      @include('admin.layout.header')
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        @include('admin.layout.sidebar')
        <!-- partial -->
        <div class="main-panel">
          @yield('content')
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block"></span>
              <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">Copyright © 2023. All rights reserved.</span>
            </div>
          </footer>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    @include('admin.layout.foot')
  </body>
</html>