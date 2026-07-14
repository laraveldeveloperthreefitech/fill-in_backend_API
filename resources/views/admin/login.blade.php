<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Star Admin2 </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/feather/feather.css')}}">
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/mdi/css/materialdesignicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/ti-icons/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/font-awesome/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/typicons/typicons.css')}}">
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/simple-line-icons/css/simple-line-icons.css')}}">
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/css/vendor.bundle.base.css')}}">
    <link rel="stylesheet" href="{{asset('admin/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{asset('admin/assets/css/style.css')}}">
    <!-- endinject -->
    <link rel="shortcut icon" href="{{asset('admin/assets/images/favicon.png')}}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css">
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
          <div class="row w-100 mx-0">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="text-center brand-logo">
                    <img src="{{ asset('admin/assets/images/logo.svg') }}" alt="logo">
                </div>

                <form action="{{route('admin.auth')}}" method="post">
                    @csrf

                    <input type="hidden" name="fcm_token" id="fcm_token">
                  <div class="form-group">
                    <input type="email" class="form-control form-control-lg"
                    name="email"
                    @if (Cookie::has('email')) value="{{ Cookie::get('email') }}" @endif
                    id="exampleInputEmail1" placeholder="Username">
                  </div>
                  <div class="form-group">
                    <input type="password" class="form-control form-control-lg" 
                    name="password"
                    @if (Cookie::has('password')) value="{{ Cookie::get('password') }}" @endif
                    id="exampleInputPassword1" placeholder="Password">
                  </div>
                  <div class="mt-3 d-grid gap-2">
                    <button class="btn btn-primary login-btn">SIGN IN</button>
                  </div>
                  <div class="my-2 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                      <label class="form-check-label text-muted">
                        <input type="checkbox"
                        name="remember"
                        id="remember"
                        @if (Cookie::has('email') && Cookie::get('email')) checked @endif
                        class="form-check-input"> Keep me signed in </label>
                    </div>
                    <a href="#" class="auth-link text-black">Forgot password?</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{asset('admin/assets/vendors/js/vendor.bundle.base.js')}}"></script>
    <script src="{{asset('admin/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{asset('admin/assets/js/off-canvas.js')}}"></script>
    <script src="{{asset('admin/assets/js/template.js')}}"></script>
    <script src="{{asset('admin/assets/js/settings.js')}}"></script>
    <script src="{{asset('admin/assets/js/hoverable-collapse.js')}}"></script>
    <script src="{{asset('admin/assets/js/todolist.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-messaging-compat.js"></script>
    <script>
        function successMsg(msg) {
                $.toast({
                    heading: 'Success',
                    text: msg,
                    showHideTransition: 'slide',
                    icon: 'success',
                    position: 'top-right',
                    loaderBg: '#f96868'
                });
            }
            function errorMsg(msg) {
                $.toast({
                      heading: 'Error',
                      text: msg,
                      showHideTransition: 'fade',
                      icon: 'error',
                      position: 'top-right',
                      loaderBg: '#f2a654'
                  });
            }
          $(document).ready(function () {
            
            @if(session('success'))
            
                successMsg("{{ session('success') }}")
              @endif

              @if(session('error'))
                errorMsg("{{ session('error') }}")
              @endif

              @if(session('info'))
                  $.toast({
                      heading: 'Info',
                      text: "{{ session('info') }}",
                      showHideTransition: 'plain',
                      icon: 'info',
                      position: 'top-right',
                      loaderBg: '#46c35f'
                  });
              @endif

              @if(session('warning'))
                  $.toast({
                      heading: 'Warning',
                      text: "{{ session('warning') }}",
                      showHideTransition: 'plain',
                      icon: 'warning',
                      position: 'top-right',
                      loaderBg: '#ffb22b'
                  });
              @endif
          });
     </script>
    @if($errors->any())
        @foreach ($errors->all() as $error)
        <script>
            $(document).ready(function () {
                errorMsg("{{ $error }}")
                return;
            });
        </script>
        @endforeach
        
    @endif
    <script>
        const firebaseConfig = {
            apiKey: "AIzaSyAo8Ozpy2ZSKTnR1K2C1rs4iq-bxj4X2oc",
            projectId: "fill-in-test",
            messagingSenderId: "1026549752959",
            appId: "1:1026549752959:web:1067aec26f25178eb00b4a",
        };

        // Initialize Firebase
        const app = firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();
        if ('serviceWorker' in navigator) {
          // First check notification permission
          if (Notification.permission === 'granted') {
              navigator.serviceWorker.register('/firebase-messaging-sw.js')
              .then((registration) => {
                  messaging.getToken({
                      vapidKey: 'BAA0ZSYk9hCzCu5jNKKYM-HYQEsdRuHh0pb3kxf8UkL-kXH_KB7lHklOl5eMQdiIFwqo_3DcBaWT3J67Nh9mLDg',
                      serviceWorkerRegistration: registration
                  }).then((currentToken) => {
                      if (currentToken) {
                          document.getElementById('fcm_token').value = currentToken;
                          console.log('✅ FCM Token:', currentToken);
                      } else {
                          console.log('⚠️ No registration token available.');
                      }
                  }).catch((err) => {
                      console.log('❌ Error retrieving token:', err);
                  });
              }).catch((err) => {
                  console.log('❌ Service worker registration failed:', err);
              });

          } else if (Notification.permission === 'default') {
              // Ask for permission only if not yet asked
              Notification.requestPermission().then((permission) => {
                  if (permission === 'granted') {
                      // Try to register again after permission granted
                      navigator.serviceWorker.register('/firebase-messaging-sw.js')
                      .then((registration) => {
                          messaging.getToken({
                              vapidKey: 'BAA0ZSYk9hCzCu5jNKKYM-HYQEsdRuHh0pb3kxf8UkL-kXH_KB7lHklOl5eMQdiIFwqo_3DcBaWT3J67Nh9mLDg',
                              serviceWorkerRegistration: registration
                          }).then((currentToken) => {
                              if (currentToken) {
                                  document.getElementById('fcm_token').value = currentToken;
                                  console.log('✅ FCM Token:', currentToken);
                              } else {
                                  console.log('⚠️ No registration token available.');
                              }
                          }).catch((err) => {
                              console.log('❌ Error retrieving token:', err);
                          });
                      }).catch((err) => {
                          console.log('❌ Service worker registration failed:', err);
                      });
                  } else {
                      console.log('❌ Notification permission denied by user.');
                  }
              });
          } else {
              console.log('❌ Notification permission denied permanently.');
          }
        }

    </script>
    <!-- endinject -->
  </body>
</html>