@extends('layouts.dashboard')
@section('title', $title)
@section('content')
    <div class="row">
        {{-- <div class="col-6 col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <a href="{{ route('article') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-agenda text-info"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Artikel</p>
                                    <h4 class="card-title">{{ $articles }}</h4>
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
                    <a href="{{ route('product') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-box-1 text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Produk</p>
                                    <h4 class="card-title">{{ $products }}</h4>
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
                    <a href="{{ route('slide') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-web text-primary"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Slide</p>
                                    <h4 class="card-title">{{ $slides }}</h4>
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
                    <a href="{{ route('gallery') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-picture text-info"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Galeri</p>
                                    <h4 class="card-title">{{ $galleries }}</h4>
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
                    <a href="{{ route('team') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-user-5 text-primary"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Team</p>
                                    <h4 class="card-title">{{ $teams }}</h4>
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
                    <a href="{{ route('review') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-chat-5 text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Review</p>
                                    <h4 class="card-title">{{ $reviews }}</h4>
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
                    <a href="{{ route('contact') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-chat-1 text-info"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Pesan Masuk</p>
                                    <h4 class="card-title">{{ $messages }}</h4>
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
                    <a href="{{ route('user') }}">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="flaticon-users text-primary"></i>
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
        </div> --}}
    </div>

@endsection
