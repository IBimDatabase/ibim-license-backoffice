<script>
    var ngHost = '{{ env('APP_URL') }}';
</script>

<!--   Core JS Files   -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/core/angular.min.js"></script>
<script src="../assets/js/core/moment.min.js"></script>
<script src="../assets/js/core/moment-timezone-with-data.min.js"></script>

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jstimezonedetect/1.0.7/jstz.min.js"></script> -->

<!-- Font Awesome Icons -->
<script src="https://kit.fontawesome.com/2cb10f09ec.js" crossorigin="anonymous"></script>

<script src="../assets/js/plugins/angular-datepicker.js"></script> 
<script src="../assets/js/plugins/angularjs-dropdown-multiselect.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../assets/js/app.js"></script>


<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>

<!-- Github buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/soft-ui-dashboard.min.js?v=1.0.2"></script>
