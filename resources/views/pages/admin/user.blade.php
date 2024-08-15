@extends('layouts.dashboard')
@section('title', $title)
@push('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/toggle-status.css') }}">
@endpush
@section('content')
    <div class="row mb-5">
        <div class="col-md-12" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">Pengguna</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                        <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah</button>
                    </div>
                    @if ($user->role != 'operator_cctv')
                        <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilter">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fRole">Filter Level</label>
                                        <select class="form-control" id="fRole" name="fRole">
                                            <option value="">All</option>
                                            <option value="superadmin">Super Admin</option>
                                            <option value="operator">Operator</option>
                                            <option value="operator_cctv">Operator CCTV</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="pt-3">
                                        <button class="mt-4 btn btn-sm btn-success mr-3" type="submit">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="userDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Nama Pengguna</th>
                                    <th class="all">Username</th>
                                    <th class="all">Email</th>
                                    <th class="all">Level</th>
                                    <th class="all">Status</th>
                                    <th class="all">Device Token</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- form --}}
        <div class="col-md-4 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Tambah / Edit</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-sm btn-warning" onclick="return closeForm(this)" id="btnCloseForm">
                            <i class="ion-android-close"></i>
                        </button>
                    </div>
                </div>
                <div class="card-block">
                    <form>
                        <input class="form-control" id="id" type="hidden" name="id" />
                        <div class="form-group">
                            <label for="name">Nama</label>
                            <input class="form-control" id="name" type="text" name="name"
                                placeholder="masukkan nama pengguna" required />
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input class="form-control" id="username" type="text" name="username"
                                placeholder="masukkan username pengguna" required />
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" id="email" type="email" name="email"
                                placeholder="masukkan email pengguna" required />
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input class="form-control" id="password" type="password" name="password"
                                placeholder="masukkan password pengguna" />
                            <small class="text-warning">Min 5 Karakter</small>
                        </div>
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control form-control" id="is_active" name="is_active" required>
                                <option value = "">Pilih Status</option>
                                <option value="Y">Aktif</option>
                                <option value="N">Disable</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="role">Level</label>
                            <select class="form-control form-control" id="role" name="role" required>
                                <option value="">Pilih Level</option>
                                <option value="superadmin">Supera Admin</option>
                                <option value="operator">Operator</option>
                                <option value="operator_cctv">Operator CCTV</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="device_token">Device Token</label>
                            <input class="form-control" id="device_token" type="text" name="device_token"
                                placeholder="masukan token image mobile phone (optional)" />
                            <small class="text-warning">Optional - hanya digunakan untuk mengubah akses mobile</small>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-sm btn-primary" type="submit" id="submit">
                                <i class="ti-save"></i><span>Simpan</span>
                            </button>
                            <button class="btn btn-sm btn-default" id="reset" type="reset"
                                style="margin-left : 10px;"><span>Reset</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- form access cctv and list cctv of user cctv --}}
        <div class="col-md-12" style="display: none" data-action="update" id="formUserCctv">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>TAMBAH AKSES USER CCTV</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-sm btn-warning" onclick="return closeForm(this)" id="btnCloseForm">
                            <i class="ion-android-close"></i>
                        </button>
                    </div>
                    <div class="mt-5">
                        <form>
                            <input class="form-control" id="user_id" type="hidden" name="user_id" />
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group mt-1">
                                        <label for="target">Target User</label>
                                        <input class="form-control" id="target" type="text" name="target" readonly />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="building_id">Area Gedung</label>
                                        <select class="form-control form-control" id="building_id" name="building_id">
                                            <option value = "">Pilih Gedung</option>
                                            @foreach ($buildings as $building)
                                                <option value = "{{ $building->id }}">{{ $building->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="floor_id">Area Lantai</label>
                                        <select class="form-control form-control" id="floor_id" name="floor_id">
                                            <option value = "">Pilih Lantai</option>
                                            {{-- request by api based on building area --}}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="cctv_id">Area CCTV</label>
                                        <select class="form-control form-control" id="cctv_id" name="cctv_id">
                                            <option value = "">Pilih Cctv</option>
                                            {{-- request by api based on building area --}}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="pt-4">
                                        <button class="mt-4 btn btn-sm btn-success mr-3" type="submit">Tambah
                                            Akses</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-block">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered nowrap dataTable" id="userCctvDataTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Area Gedung</th>
                                    <th class="all">Area Lantai</th>
                                    <th class="all">Nama CCTV</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>
    <script>
        let dTable = null;
        let dTableUserCctv = null;

        $('#building_id,#floor_id,#cctv_id').select2({
            theme: "bootstrap"
        });

        $(function() {
            dataTable();
        })

        function dataTable(filter) {
            let url = "/api/admin/user/datatable";
            if (filter) url += '?' + filter;

            dTable = $("#userDataTable").DataTable({
                searching: true,
                orderng: true,
                lengthChange: true,
                responsive: true,
                processing: true,
                serverSide: true,
                searchDelay: 1000,
                paging: true,
                lengthMenu: [5, 10, 25, 50, 100],
                ajax: url,
                columns: [{
                    data: "action"
                }, {
                    data: "name"
                }, {
                    data: "username"
                }, {
                    data: "email"
                }, {
                    data: "role"
                }, {
                    data: "is_active"
                }, {
                    data: "device_token"
                }],
                pageLength: 10,
            });
        }

        $('#formFilter').submit(function(e) {
            e.preventDefault()
            let dataFilter = {
                role: $("#fRole").val(),
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })

        function refreshData() {
            dTable.ajax.reload(null, false);
        }

        function refreshDataUserCctv() {
            console.log("reload")
            dTableUserCctv.ajax.reload(null, false);
        }

        function addData() {
            $("#username").removeAttr("readonly");
            $("#formEditable").attr('data-action', 'add').fadeIn(200);
            $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
            $("#name").focus();
        }

        function closeForm() {
            $("#username").removeAttr("readonly");
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-8").addClass("col-md-12").fadeIn(200)
                $("#reset").click();
                $("#formUserCctv").slideUp(200)
            })
        }

        function getData(id) {
            $.ajax({
                url: `/api/admin/user/${id}/detail`,
                method: "GET",
                dataType: "json",
                success: function(res) {
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
                        let d = res.data;
                        $("#id").val(d.id);
                        $("#name").val(d.name);
                        $("#username").val(d.username);
                        $("#username").attr("readonly", true);
                        $("#email").val(d.email);
                        $("#password").val(d.password);
                        $("#role").val(d.role);
                        $("#is_active").val(d.is_active);
                        $("#device_token").val(d.device_token);
                    })
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("warning", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        $("#formEditable form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append("id", parseInt($("#id").val()));
            formData.append("name", $("#name").val());
            formData.append("username", $("#username").val());
            formData.append("email", $("#email").val());
            formData.append("password", $("#password").val());
            formData.append("role", $("#role").val());
            formData.append("is_active", $("#is_active").val());
            formData.append("device_token", $("#device_token").val());

            saveData(formData, $("#formEditable").attr("data-action"));
            return false;
        });

        function updateStatus(id, status) {
            let c = confirm(`Anda yakin ingin mengubah status ke ${status} ?`)
            if (c) {
                let dataToSend = new FormData();
                dataToSend.append("is_active", status == "Disabled" ? "N" : "Y");
                dataToSend.append("id", id);
                updateStatusData(dataToSend);
            }
        }

        function saveData(data, action) {
            $.ajax({
                url: action == "update" ? "/api/admin/user/update" : "/api/admin/user/create",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    closeForm();
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function removeData(id) {
            let c = confirm("Apakah anda yakin untuk menghapus data ini ?");
            if (c) {
                $.ajax({
                    url: "/api/admin/user/delete",
                    method: "DELETE",
                    data: {
                        id: id
                    },
                    beforeSend: function() {
                        console.log("Loading...")
                    },
                    success: function(res) {
                        refreshData();
                        showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    },
                    error: function(err) {
                        console.log("error :", err);
                        showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                            ?.message);
                    }
                })
            }
        }

        function updateStatusData(data) {
            $.ajax({
                url: "/api/admin/user/update-status",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshData();
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function addCctv(id, username) {
            $("#formUserCctv").fadeIn(200, function() {
                $("#boxTable").slideUp(200)
                $("#user_id").val(id);
                $("#target").val(username)
            })
            if(dTableUserCctv){
                dTableUserCctv.clear();
                dTableUserCctv.destroy();
            }
            dataTableUserCctv(id);
        }

        function dataTableUserCctv(id) {
            let url = `/api/admin/user-cctv/datatable?user_id=${id}`;

            dTableUserCctv = $("#userCctvDataTable").DataTable({
                searching: false,
                orderng: true,
                lengthChange: true,
                responsive: true,
                processing: true,
                serverSide: true,
                searchDelay: 1000,
                paging: true,
                lengthMenu: [5, 10, 25, 50, 100],
                ajax: url,
                columns: [{
                    data: "action"
                }, {
                    data: 'building',
                    "render": function(data, type, row, meta) {
                        if (type === 'display') {
                            if (data == 'Data Terhapus') {
                                return `<div class="badge badge-danger">${data}</div>`;
                            } else {
                                data
                            }
                        }
                        return data;
                    }
                }, {
                    data: 'floor',
                    "render": function(data, type, row, meta) {
                        if (type === 'display') {
                            if (data == 'Data Terhapus') {
                                return `<div class="badge badge-danger">${data}</div>`;
                            } else {
                                data
                            }
                        }
                        return data;
                    }
                }, {
                    data: 'cctv',
                    "render": function(data, type, row, meta) {
                        if (type === 'display') {
                            if (data == 'Data Terhapus') {
                                return `<div class="badge badge-danger">${data}</div>`;
                            } else {
                                data
                            }
                        }
                        return data;
                    }
                }],
                pageLength: 10,
            });
        }

        $("#building_id").change(function() {
            let building_id = $(this).val();
            getFloorList(building_id);
            // reset cctv
            $("#cctv_id").empty();
            $('#cctv_id').append("<option value =''>Pilih Cctv</option > ");
        })

        function getFloorList(building_id) {
            $.ajax({
                url: `/api/admin/floor/list?building_id=${building_id}`,
                method: "GET",
                header: {
                    "Content-Type": "application/json"
                },
                beforeSend: function() {
                    console.log("Sending data...!")
                },
                success: function(res) {
                    // update input form
                    $("#floor_id").empty();
                    $('#floor_id').append("<option value =''>Pilih Lantai</option > ");
                    $.each(res.data, function(index, r) {
                        $('#floor_id').append("<option value = '" + r.id + "' > " + r
                            .name + " </option > ");
                    })
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            })
        }

        $("#floor_id").change(function() {
            let floor_id = $(this).val();
            getCctvList(floor_id);
        })

        function getCctvList(floor_id) {
            $.ajax({
                url: `/api/admin/cctv/list?floor_id=${floor_id}`,
                method: "GET",
                header: {
                    "Content-Type": "application/json"
                },
                beforeSend: function() {
                    console.log("Sending data...!")
                },
                success: function(res) {
                    // update input form
                    $("#cctv_id").empty();
                    $('#cctv_id').append("<option value =''>Pilih Cctv</option > ");
                    $.each(res.data, function(index, r) {
                        $('#cctv_id').append("<option value = '" + r.id + "' > " + r
                            .name + " </option > ");
                    })
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            })
        }

        $("#formUserCctv form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append("user_id", parseInt($("#user_id").val()));
            formData.append("cctv_id", parseInt($("#cctv_id").val()));

            saveDataUserCctv(formData, $("#formEditable").attr("data-action"));
            return false;
        });

        function saveDataUserCctv(data, action) {
            $.ajax({
                url: "/api/admin/user-cctv/create",
                contentType: false,
                processData: false,
                method: "POST",
                data: data,
                beforeSend: function() {
                    console.log("Loading...")
                },
                success: function(res) {
                    showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    refreshDataUserCctv();
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                        ?.message);
                }
            })
        }

        function removeDataUserCctv(id) {
            let c = confirm("Apakah anda yakin untuk menghapus data ini ?");
            if (c) {
                $.ajax({
                    url: "/api/admin/user-cctv/delete",
                    method: "DELETE",
                    data: {
                        id: id
                    },
                    beforeSend: function() {
                        console.log("Loading...")
                    },
                    success: function(res) {
                        refreshDataUserCctv();
                        showMessage("success", "flaticon-alarm-1", "Sukses", res.message);
                    },
                    error: function(err) {
                        console.log("error :", err);
                        showMessage("danger", "flaticon-error", "Peringatan", err.message || err.responseJSON
                            ?.message);
                    }
                })
            }
        }
    </script>
@endpush
