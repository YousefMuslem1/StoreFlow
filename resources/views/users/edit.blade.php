@extends('layouts.app')

@section('content')
@if (session('success'))
    <div class="alert alert-success">تم تحديث البيانات بنجاح</div>
@endif
<div class="container">
    <h2>تعديل مستخدم</h2>
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">اسم المستخدم</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
        </div>

        <div class="form-group">
            <label for="password">كلمة المرور الجديدة</label>
            <input type="text" class="form-control" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">حفظ</button>
    </form>
</div>
@endsection
