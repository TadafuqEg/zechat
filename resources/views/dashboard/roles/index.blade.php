@extends('dashboard.layout.app')
@section('title', 'Roles')
@section('content')
    <div class="container-fluid pt-4 px-4">
        
            <div class="row g-4">
                <div class="col-12">
                    <div class="bg-secondary rounded h-100 p-4">
                        <div style="display: flex;justify-content: space-between;">
                            <h6 class="mb-4">Roles Table</h6>
                            @can('create roles')
                                <a type="button" href="{{url('/roles/create')}}" class="btn btn-outline-info m-2" style="margin-top:-0.5% !important; float:right;">Create Role</a>
                            @endcan
                        </div>
                        @if(!empty($roles) && $roles->count())
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            
                                            <th scope="col">Permissions</th>
                                            
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $counter = $roles->firstItem(); // Get the starting index of the current page
                                    @endphp
                                    <tbody>
                                        @foreach($roles as $key => $role)
                                        <tr>
                                            <th scope="row">{{$counter++}}</th>
                                            <td>{{$role->name}}</td>
                                            <td>
                                                @foreach ($role->permissions as $key=>$permission)
                                                    <span class="badge badge-secondary" style="background-color:black;">{{ ucwords($permission->name) }}</span>
                                                    @if(($key+1) % 3 == 0)
                                                    <br>
                                                    @endif
                                                @endforeach
                                            </td>
                                            
                                        
                                            <td style="text-align:center;">
                                                @can('edit roles')
                                                    <a href="{{url('/role/edit/'.$role->id)}}" style="margin-right: 1rem;">
                                                    <span  class="bi bi-pen" style="font-size: 1rem; color: rgb(0,255,0);" title="Edit"></span>
                                                    </a>
                                                @endcan
                                                
                                            
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                            </div>
                            <div class="jsgrid-grid-body"style="text-align: center;padding-left:45%;">
                                {!! $roles->links("pagination::bootstrap-4") !!}

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