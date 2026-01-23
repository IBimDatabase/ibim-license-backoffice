@extends('layouts.main')
@section('content')
<div class="container-fluid py-4" ng-controller="userController">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Tests</h5>
                        </div>
                        <a href="javascript:void(0)" class="btn bg-gradient-primary mb-0" type="button" data-bs-toggle="modal" data-bs-target="#addUserModal" ng-click="clearFormData('addUserForm')"><i class="fa fa-plus-circle"></i> Add User </a>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>
@endsection
