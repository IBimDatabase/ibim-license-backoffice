<div class="notification-alert alert alert-success alert-dismissible fade">
    <button type="button" class="btn-close" ng-click="closeAlert('Success')"></button>
    <div class="text-white alert-message"></div>
</div>

<div class="notification-alert alert alert-warning alert-dismissible fade">
    <button type="button" class="btn-close" ng-click="closeAlert('Failure')"></button>
    <div class="text-white alert-message"></div>
</div>

<footer class="footer pb-4">
    <div class="container-fluid">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="copyright text-center text-sm text-muted">
                    &copy; {{ now()->year }} <a style="color: #252f40;" href="{{Route('dashboard')}}" class="font-weight-bold ml-1">IBIM Consulting.</a> All rights reserved.
                </div>
            </div>
        </div>
    </div>
</footer>