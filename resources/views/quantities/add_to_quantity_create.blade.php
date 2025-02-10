@extends('layouts.app')

@section('content')
    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    @if (session('error_message'))
        <div class="alert alert-danger">
            {{ session('error_message') }}
        </div>
    @endif
    <nav aria-label="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('quantities.index') }}">جميع الكميّات</a></li>
                <li class="breadcrumb-item active" aria-current="page">إضافة كميّة لصنف موجود مسبقاً</li>
            </ol>
        </nav>
    </nav>

    <hr>
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('quantities.add_to_existing_quantity') }}" method="POST">
                @csrf
                <div class="row">
                    {{-- col-4 --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="weight"> الوزن بالغرام<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="weight" name="weight" value="{{ old('weight') }}"
                                class="form-control {{ $errors->has('weight') ? ' is-invalid' : '' }}" placeholder="2"
                                autocomplete="off" autofocus>
                        </div>
                    </div>
                    {{-- col-6 --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">{{ __('types.type') }}<span class="text-danger">*</span></label>
                            <select name="type" id="type" required
                                class="type-select  custom-select {{ $errors->has('type') ? ' is-invalid' : '' }}"
                                value="{{ old('type') }}">
                                <option value="">إختر من هنا</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @if (old('type') == $type->id) selected @endif>
                                        {{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- col-2 --}}
                </div>

                {{-- //row --}}
                <div class="row">
                    {{-- col-6 --}}
                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            <label for="caliber">{{ __('caliber.caliber') }}<span class="text-danger">*</span></label>
                            <select name="caliber" id="caliber"
                                class="custom-select {{ $errors->has('caliber') ? ' is-invalid' : '' }}"
                                value="{{ old('caliber') }}">
                                <option value="">إختر من هنا</option>
                                @foreach ($calibers as $caliber)
                                    <option value="{{ $caliber->id }}" @if (old('caliber') == $caliber->id) selected @endif>
                                        {{ $caliber->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> --}}
                    {{-- col-6 --}}
                    {{-- <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">{{ __('types.type') }}<span class="text-danger">*</span></label>
                            <select name="type" id="type" 
                                class="type-select  custom-select {{ $errors->has('type') ? ' is-invalid' : '' }}"
                                value="{{ old('type') }}">
                                <option value="">إختر من هنا</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @if (old('type') == $type->id) selected @endif>
                                        {{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> --}}
                </div>
                <button type="submit" class="btn btn-success btn-lg">{{ __('buttons.save') }}</button>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.type-select').select2();
        });
        // prevent submitting form when Enter Clicked
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('createProductForm').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
