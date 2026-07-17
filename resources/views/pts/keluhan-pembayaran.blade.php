@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<div class="md2-page-header">
    <div class="page-titles">
        <h3>Keluhan Pembayaran</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Lainnya</a></li>
                <li class="breadcrumb-item active">Keluhan Pembayaran</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card">
    <div class="card-body px-4 pb-4 pt-4">
            <div class="d-flex justify-content-end align-items-center mb-3 px-3">
                <div class="input-group me-3" id="DataTables_Table_0_filter" style="max-width: 200px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="search" class="form-control" id="searchInput" placeholder="Search..."
                        aria-controls="DataTables_Table_0">
                </div>
                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasAddUser">
                    <i class="bx bx-plus bx-sm me-1"></i><span class="d-none d-sm-inline-block">Tambah</span>
                </button>
            </div>

            <div class="table-responsive text-nowrap border rounded mb-4">
            <table class="table table-hover md2-table m-0">
                <thead style="background-color: #dbdee0;">
                    <tr>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <tr>
                        <td><i class="fab fa-angular fa-xl text-danger me-4"></i> <span>Angular Project</span></td>
                        <td>Albert Cook</td>
                        <td>
                            <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Lilian Fuller"
                                    data-bs-original-title="Lilian Fuller">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/5.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Sophia Wilkerson"
                                    data-bs-original-title="Sophia Wilkerson">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/6.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Christina Parker"
                                    data-bs-original-title="Christina Parker">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/7.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                            </ul>
                        </td>
                        <td><span class="badge bg-label-primary me-1">Active</span></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-edit-alt me-1"></i>
                                        Edit</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-1"></i>
                                        Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fab fa-react fa-xl text-info me-4"></i> <span>React Project</span></td>
                        <td>Barry Hunter</td>
                        <td>
                            <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Lilian Fuller"
                                    data-bs-original-title="Lilian Fuller">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/5.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Sophia Wilkerson"
                                    data-bs-original-title="Sophia Wilkerson">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/6.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Christina Parker"
                                    data-bs-original-title="Christina Parker">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/7.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                            </ul>
                        </td>
                        <td><span class="badge bg-label-success me-1">Completed</span></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="bx bx-edit-alt me-2"></i> Edit</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-2"></i>
                                        Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fab fa-vuejs fa-xl text-success me-4"></i> <span>VueJs Project</span></td>
                        <td>Trevor Baker</td>
                        <td>
                            <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Lilian Fuller"
                                    data-bs-original-title="Lilian Fuller">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/5.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Sophia Wilkerson"
                                    data-bs-original-title="Sophia Wilkerson">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/6.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Christina Parker"
                                    data-bs-original-title="Christina Parker">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/7.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                            </ul>
                        </td>
                        <td><span class="badge bg-label-info me-1">Scheduled</span></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="bx bx-edit-alt me-2"></i> Edit</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-2"></i>
                                        Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fab fa-bootstrap fa-xl text-primary me-4"></i> <span>Bootstrap Project</span></td>
                        <td>Jerry Milton</td>
                        <td>
                            <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Lilian Fuller"
                                    data-bs-original-title="Lilian Fuller">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/5.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Sophia Wilkerson"
                                    data-bs-original-title="Sophia Wilkerson">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/6.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                    class="avatar avatar-xs pull-up" aria-label="Christina Parker"
                                    data-bs-original-title="Christina Parker">
                                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/assets/img/avatars/7.png"
                                        alt="Avatar" class="rounded-circle">
                                </li>
                            </ul>
                        </td>
                        <td><span class="badge bg-label-warning me-1">Pending</span></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                            class="bx bx-edit-alt me-2"></i> Edit</a>
                                    <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-trash me-2"></i>
                                        Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
