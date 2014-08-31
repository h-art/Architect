@extends('architect::layouts/master')

@section('content')
    <h1>Show {{ $eloquent_model }}</h1>

    @foreach ( $fields as $field_name )
        <h3>{{ $controller->renderLabel($field_name) }}</h3>
        <p>{{ $controller->renderField('show', $row, $field_name, $row->$field_name) }}</p>
    @endforeach

    {{ Form::open(['route' => [strtolower($eloquent_model) . '.destroy', $row->id], 'method' => 'delete']) }}
        <div class="btn-group">
            {{ link_to_route(strtolower($eloquent_model) . '.index', 'Back to list', NULL, ['class' => 'btn btn-primary']) }}
            {{ link_to_route(strtolower($eloquent_model) . '.edit', 'Edit', [strtolower($eloquent_model) => $row->id], ['class' => 'btn btn-warning']) }}
            {{ Form::submit('Destroy', ['class' => 'btn btn-danger', 'onclick' => 'return confirm(\'Sure?\')']) }}
        </div>
    {{ Form::close() }}
@endsection
