@extends('layouts.dashboard')
@section('title', $title)
@section('content')
    <div class="row">
        @if ($user->role != 'operator_gedung')
            <div class="col-6 col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <a href="{{ route('building') }}">
                            <div class="row">
                                <div class="col-5">
                                    <div class="icon-big text-center">
                                        <i class="flaticon-store text-info"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Gedung</p>
                                        <h4 class="card-title">{{ $buildings }}</h4>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="col-6 col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <a href="{{ route('building') }}">
                            <div class="row">
                                <div class="col-5">
                                    <div class="icon-big text-center">
                                        <i class="flaticon-store text-info"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Gedung</p>
                                        <h4 class="card-title">{{ $buildingName }}</h4>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-6 col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body ">
                    <a href="{{ route('floor') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-layers text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Lantai</p>
                                    <h4 class="card-title">{{ $floors }}</h4>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body ">
                    <a href="{{ route('cctv') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-photo-camera text-primary"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">CCTV</p>
                                    <h4 class="card-title">{{ $cctvs }}</h4>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        @if ($user->role != 'operator_gedung')
            <div class="col-6 col-sm-6 col-md-3">
                <div class="card card-stats card-round">
                    <div class="card-body ">
                        <a href="{{ route('user') }}">
                            <div class="row">
                                <div class="col-5">
                                    <div class="icon-big text-center">
                                        <i class="flaticon-users text-info"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-stats">
                                    <div class="numbers">
                                        <p class="card-category">Pengguna</p>
                                        <h4 class="card-title">{{ $users }}</h4>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection
