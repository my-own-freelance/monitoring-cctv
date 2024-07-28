@extends('layouts.dashboard')
@section('title', $title)

@section('content')
    <div class="row mb-5">
        <div class="col-md-12" id="boxTable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5 class="text-uppercase title">Data CCTV</h5>
                    </div>
                    <div class="card-header-right">
                        <button class="btn btn-mini btn-info mr-1" onclick="return refreshData();">Refresh</button>
                        @if ($user->role == 'superadmin')
                            <button class="btn btn-mini btn-primary" onclick="return addData();">Tambah Data</button>
                        @endif
                    </div>
                    @if ($user->role != 'operator_cctv')
                        <form class="navbar-left navbar-form mr-md-1 mt-3" id="formFilter">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fBuilding">Filter Gedung</label>
                                        <select class="form-control" id="fBuilding" name="fBuilding">
                                            <option value="">All</option>
                                            @foreach ($buildings as $building)
                                                <option value = "{{ $building->id }}">{{ $building->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fFloor">Filter Lantai</label>
                                        <select class="form-control" id="fFloor" name="fFloor">
                                            <option value="">All</option>
                                            {{-- request by api based on filter building  --}}
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
                        <table class="table table-striped table-bordered nowrap dataTable" id="cctvTable">
                            <thead>
                                <tr>
                                    <th class="all">#</th>
                                    <th class="all">Nama CCTV</th>
                                    <th class="all">Area Gedung</th>
                                    <th class="all">Area Lantai</th>
                                    <th class="all">Url Cctv</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center"><small>Tidak Ada Data</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12" style="display: none" data-action="update" id="formEditable">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h5>Tambah / Edit Data</h5>
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
                            <label for="name">Nama CCTV</label>
                            <input class="form-control" id="name" type="text" name="name"
                                placeholder="masukkan nama cctv" required />
                        </div>
                        <div class="form-group">
                            <label for="url">URL CCTV</label>
                            <input class="form-control" id="url" type="text" name="url"
                                placeholder="masukkan url cctv" required />
                        </div>
                        <div class="form-group">
                            <label for="building_id">Area Gedung</label>
                            <select class="form-control form-control" id="building_id" name="building_id">
                                <option value = "">Pilih Gedung</option>
                                @foreach ($buildings as $building)
                                    <option value = "{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="floor_id">Area Lantai</label>
                            <select class="form-control form-control" id="floor_id" name="floor_id">
                                <option value = "">Pilih Lantai</option>
                                {{-- request by api based on building area --}}
                            </select>
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
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dashboard/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('dashboard/js/plugin/select2/select2.full.min.js') }}"></script>
    <script>
        let dTable = null;

        $('#fBuilding,#fFloor,#building_id,#floor_id').select2({
            theme: "bootstrap"
        });

        $(function() {
            dataTable();
        })

        function dataTable(filter) {
            let url = "/api/admin/cctv/datatable";
            if (filter) url += '?' + filter;

            dTable = $("#cctvTable").DataTable({
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
                    data: "building.name"
                }, {
                    data: "floor.name"
                }, {
                    data: "url"
                }],
                pageLength: 10,
            });
        }

        $('#formFilter').submit(function(e) {
            e.preventDefault()
            let dataFilter = {
                building_id: $("#fBuilding").val(),
                floor_id: $("#fFloor").val(),
            }

            dTable.clear();
            dTable.destroy();
            dataTable($.param(dataFilter))
            return false
        })

        function refreshData() {
            dTable.ajax.reload(null, false);
        }


        function addData() {
            $("#formEditable").attr('data-action', 'add').fadeIn(200);
            $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
            $("#name").focus();
        }

        function closeForm() {
            $("#formEditable").slideUp(200, function() {
                $("#boxTable").removeClass("col-md-8").addClass("col-md-12");
                $("#reset").click();
            })
        }

        function getData(id) {
            $.ajax({
                url: `/api/admin/cctv/${id}/detail`,
                method: "GET",
                dataType: "json",
                success: function(res) {
                    $("#formEditable").attr("data-action", "update").fadeIn(200, function() {
                        $("#boxTable").removeClass("col-md-12").addClass("col-md-8");
                        let d = res.data;
                        $("#id").val(d.id);
                        $("#name").val(d.name);
                        $("#url").val(d.url);
                        $("#building_id").val(d.building_id).change();

                        // ambil data lantai gedung
                        getFloorList(d.building_id, d.floor_id)
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
            formData.append("url", $("#url").val());
            formData.append("building_id", parseInt($("#building_id").val()));
            formData.append("floor_id", parseInt($("#floor_id").val()));

            saveData(formData, $("#formEditable").attr("data-action"));
            return false;
        });

        function saveData(data, action) {
            $.ajax({
                url: action == "update" ? "/api/admin/cctv/update" : "/api/admin/cctv/create",
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
                    url: "/api/admin/cctv/delete",
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

        $("#building_id").change(function() {
            let building_id = $(this).val();
            getFloorList(building_id);
        })

        $("#fBuilding").change(function() {
            let building_id = $(this).val();
            getFloorList(building_id, null, true);
        })

        function getFloorList(building_id, floor_id = null, for_filter = false) {
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
                    if (!for_filter) {
                        $("#floor_id").empty();
                        $('#floor_id').append("<option value =''>Pilih Lantai</option > ");
                        $.each(res.data, function(index, r) {
                            $('#floor_id').append("<option value = '" + r.id + "' > " + r
                                .name + " </option > ");
                        })

                        if (floor_id) {
                            $("#floor_id").val(floor_id).change();
                        }
                    } else {
                        // update filter data
                        $("#fFloor").empty();
                        $('#fFloor').append("<option value =''>All</option > ");
                        $.each(res.data, function(index, r) {
                            $('#fFloor').append("<option value = '" + r.id + "' > " + r
                                .name + " </option > ");
                        })
                    }
                },
                error: function(err) {
                    console.log("error :", err);
                    showMessage("danger", "flaticon-error", "Peringatan", err.message || err
                        .responseJSON
                        ?.message);
                }
            })
        }
    </script>
@endpush
