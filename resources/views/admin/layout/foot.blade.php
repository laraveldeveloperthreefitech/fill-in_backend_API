<script src="{{asset('admin/assets/vendors/js/vendor.bundle.base.js')}}"></script>
    <script src="{{asset('admin/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
    <!-- endinject -->
     <!-- Moment.js (Required for Date Range Picker) -->
    <script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <!-- Plugin js for this page -->
    <script src="{{asset('admin/assets/vendors/chart.js/chart.umd.js')}}"></script>
    <script src="{{asset('admin/assets/vendors/progressbar.js/progressbar.min.js')}}"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{asset('admin/assets/js/off-canvas.js')}}"></script>
    <script src="{{asset('admin/assets/js/template.js')}}"></script>
    <script src="{{asset('admin/assets/js/settings.js')}}"></script>
    <script src="{{asset('admin/assets/js/hoverable-collapse.js')}}"></script>
    <script src="{{asset('admin/assets/js/todolist.js')}}"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="{{asset('admin/assets/js/jquery.cookie.js')}}" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>
    <script src="{{asset('admin/assets/js/dashboard.js')}}"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-messaging-compat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
    
    <!-- <script src="assets/js/Chart.roundedBarCharts.js"></script> -->
    <!-- End custom js for this page-->
    <script>
        $(document).ready(function() {
            $('#date-range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });

            $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            $("#check-all").on("change", function() {
                $(".child-checkbox").prop("checked", this.checked);
            });
        });
    </script>
    @stack('script')
    <!-- <script>
         const firebaseConfig = {
            apiKey: "AIzaSyAo8Ozpy2ZSKTnR1K2C1rs4iq-bxj4X2oc",
            projectId: "fill-in-test",
            messagingSenderId: "1026549752959",
            appId: "1:1026549752959:web:1067aec26f25178eb00b4a",
        };

        // Initialize Firebase
        const app       = firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();
        messaging.onMessage((payload) => {
            console.log('Message received: ', payload);
            new Notification(payload.notification.title, {
            body: payload.notification.body,
            icon: '/icon.png'
            });
        });   
    </script> -->
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

        // Only log foreground messages (optional)
        messaging.onMessage((payload) => {
            console.log('Foreground message received: ', payload);
            fetchNotifications();
            // ❌ Do NOT show notification manually here if using service worker
        });
    </script>

    <script>
    dayjs.extend(dayjs_plugin_relativeTime);
    // alert('hi');
    function fetchNotifications() {
        $.ajax({
            url: '{{url("admin/notifications/fetch")}}',
            method: 'GET',
            success: function(notifications) {
                let html = '';

                if (notifications.length === 0) {
                    html = `<li class="list-group-item text-center text-muted">No notifications found.</li>`;
                } else {
                    let unreadCount = notifications.filter(notification => notification.read_at === null).length;
                    $('.count').html(unreadCount);

                    html += `<a class="dropdown-item py-3 border-bottom">
                    <p class="mb-0 fw-medium float-start">You have ${unreadCount} new notifications </p>
                    </a>`;
                    notifications.forEach(notification => {
                    let isRead = notification.read_at !== null;
                    html += `
                        <div class="border-bottom py-2 px-3 ${isRead ? 'text-muted' : 'bg-light'}">
                            <h6 class="mb-1"><b>${notification.data.title}</b></h6>
                            <p class="mb-1">${notification.data.message}</p>
                            <small>${timeAgo(notification.created_at)}</small>
                            <div>
                                <a href="javascript:void(0)" data-url="${notification.data.redirect_url}"
                            data-id="${notification.id}" 
                            class="btn btn-sm btn-outline-primary mt-2 notification-link">View</a>
                            </div>
                        </div>
                    `;
                });
                }
                // html += `<a href="javascript:void(0)" id="see-all-notifications" 
                //     class="dropdown-item text-center text-primary fw-bold py-2">
                //     See All Notifications
                // </a>`;

                $('#notification-scroll').html(html);

            }
        });
    }

    // Convert timestamp to "x mins ago" style
    function timeAgo(datetime) {
        return dayjs(datetime).fromNow(); // Same as diffForHumans()
    }

    // Initial fetch
    fetchNotifications();

    // $(document).on('click', '#see-all-notifications', function () {
        
    //     $.ajax({
    //         url: '/admin/notifications/all',
    //         method: 'GET',
    //         success: function (notifications) {
    //             let html = '';
    //             $('#notification-scroll').empty();
    //             html += `<a class="dropdown-item py-3 border-bottom">
    //                 <p class="mb-0 fw-medium float-start">You have ${notifications.length} new notifications </p>
    //                 </a>`;
    //             notifications.forEach(notification => {
    //                 let isRead = notification.read_at !== null;
    //                 html += `
    //                     <div class="border-bottom py-2 px-3 ${isRead ? 'text-muted' : 'bg-light'}">
    //                         <h6 class="mb-1">${notification.data.title}</h6>
    //                         <p class="mb-1">${notification.data.message}</p>
    //                         <small>${timeAgo(notification.created_at)}</small>
    //                         <div>
    //                             <a href="${notification.data.redirect_url}" class="btn btn-sm btn-outline-primary mt-2">View</a>
    //                         </div>
    //                     </div>
    //                 `;
    //             });

    //             // Load into a modal or container
    //             $('#notification-scroll').html(html);

    //             $('#allNotificationsModal').modal('show');
    //         }
    //     });
    // });


    // Fetch every 30 seconds
    // setInterval(fetchNotifications, 30000);
</script>
<!-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Echo !== 'undefined') {
                Echo.private('App.Models.User.{{ Auth::id() }}')
                    .notification((notification) => {
                        fetchNotifications();
                    });
            } else {
                console.warn("Echo is not available!");
            }
        });
    </script> -->
    <script>
         $(document).ready(function() {
             $(document).on('click', '.notification-link', function(e) {
                e.preventDefault(); // Fix: define the event parameter

                var notificationId = $(this).data('id');
                var redirectUrl = $(this).data('url');
                $.ajax({
                    url: `/admin/notifications/mark-as-read/${notificationId}`, // Fix: plain JS template string here
                    method: 'GET',
                    success: function(response) {
                        if(redirectUrl){
                            window.location.href = redirectUrl;
                        }else{
                            window.location.reload();

                        }
                        
                    },
                    error: function() {
                        // Optional: fallback in case of error
                        // window.location.href = redirectUrl;
                    }
                });
            });
        });
    </script>

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
     <script>
        document.addEventListener("DOMContentLoaded", function () {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

            // Send timezone to Laravel via AJAX or form
            fetch('/store-timezone', {
                method: 'post',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ timezone: timezone })
            });
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