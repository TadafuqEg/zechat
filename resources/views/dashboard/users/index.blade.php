@extends('dashboard.layout.app')
@section('title', 'Users')
@section('content')
    <div class="container-fluid pt-4 px-4">
        
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <div style="display: flex;justify-content: space-between;">
                            <h6 class="mb-4">Users Table</h6>
                            @can('create users')
                                <a type="button" href="{{url('/users/create')}}" class="btn btn-outline-info m-2" style="margin-top:-0.5% !important; float:right;">Create User</a>
                            @endcan
                        </div>
                        @if(!empty($all_users) && $all_users->count())
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            
                                            <th scope="col">Email</th>
                                            <th scope="col">Section</th>
                                            <th scope="col">Role</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $counter = $all_users->firstItem(); // Get the starting index of the current page
                                    @endphp
                                    <tbody>
                                        @foreach($all_users as $key => $user)
                                        <tr>
                                            <th scope="row">{{$counter++}}</th>
                                            <td style="display: flex;"> <div class="position-relative">
                                                <img class="rounded-circle" src="{{asset('logos/user_logo.png')}}" alt="" style="width: 40px; height: 40px;">
                                                <div class="@if($user->is_online==1) bg-success @else bg-wrong @endif rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                                            </div>
                                            <div class="ms-3" style="height: 3%; margin-top:4%;">
                                                <h6 class="mb-0">{{$user->name}}</h6>
                                                
                                            </div></td>
                                            <td>{{$user->email}}</td>
                                            <td>@if($user->section_id) {{$user->section->name}} @else _ @endif </td>
                                            <td>{{ucwords($user->guard)}}</td>
                                        
                                            <td>
                                                @can('edit users')
                                                <a href="{{url('/user/edit/'.$user->id)}}" style="margin-right: 1rem;">
                                                <span  class="bi bi-pen" style="font-size: 1rem; color: rgb(0,255,0);" title="Edit"></span>
                                                </a>
                                                @endcan
                                                @can('delete users')
                                                <a href="{{url('/user/delete/'.$user->id)}}">
                                                    <span class="bi bi-trash" style="font-size: 1rem; color: rgb(255,0,0);" title="Delete"></span>
                                                </a>
                                                @endcan
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                            </div>
                            <div class="jsgrid-grid-body"style="text-align: center;padding-left:45%;">
                                {!! $all_users->links("pagination::bootstrap-4") !!}

                            </div>
                        @else
                            
                                <div class="row vh-100 bg-secondary rounded align-items-center justify-content-center mx-0">
                                    <div class="col-md-6 text-center">
                                        <h3>This is blank page</h3>
                                    </div>
                                </div>
                            
                        @endif
                    </div>
                </div>
            </div>
        
    </div>
@endsection
@push('scripts')
@endpush