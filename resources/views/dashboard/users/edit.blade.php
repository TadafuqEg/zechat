@extends('dashboard.layout.app')
@section('title', 'Edit User')
@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-secondary rounded h-100 p-4">
                <h6 class="mb-4">Edit User</h6>
                <form action="{{ route('update.user',$user) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputEmail3"  name="name" value="{{ old('name',$user->name) }}">
                            @if ($errors->has('name'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('name') }}</p>
                        @endif
                        </div>
                    </div>
                    
                   
                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="email" class="form-control" id="inputEmail3"  name="email" value="{{ old('email',$user->email) }}">
                            @if ($errors->has('email'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('email') }}</p>
                        @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPassword3" class="col-sm-2 col-form-label">Role</label>
                        <div class="col-sm-10">
                            <select class="form-select" id="floatingSelect" name="role"
                                aria-label="Floating label select example">
                                
                                @foreach($roles as $role)
                                <option value="{{$role->id}}"  @if(old('role')==$role->id || $user->roles->first()->id==$role->id) selected @endif>{{ucwords($role->name)}}</option>
                                @endforeach
                               
                            </select>
                            @if ($errors->has('role'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('role') }}</p>
                        @endif
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
@endpush