@extends('architect::layouts/master')

@section('content')
    <h1>Edit {{ $eloquent_model }}</h1>

    {{ Form::open(['route' => [strtolower($eloquent_model) . '.update', $row->id], 'method' => 'put', 'role' => 'form', 'class' => 'form-horizontal']) }}
        @foreach ( $fields as $field_name )
            <div class="form-group">
                {{ Form::label($controller->renderLabel($field_name), NULL, ['class' => 'col-sm-2']) }}
                <div class="col-sm-10">
                    {{ $controller->renderField('edit', $row, $field_name, $row->$field_name) }}
                </div>
            </div>
        @endforeach
        <div class="btn-group">
            {{ link_to_route(strtolower($eloquent_model) . '.index', 'Back to list', NULL, ['class' => 'btn btn-primary']) }}
            {{ Form::submit('Update', ['class' => 'btn btn-success']) }}
        </div>
    {{ Form::close() }}
@endsection
