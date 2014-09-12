@extends('architect::layouts/master')

@section('content')
    <h1>List all {{ $eloquent_model }}</h1>

    {{ link_to_route(strtolower($eloquent_model) . '.create', 'Create new', NULL, ['class' => 'btn btn-primary']) }}

    @if(count($controller->getCustomListActions()))
{{ count($controller->getCustomListActions())}}
        @include('architect::partials.list_actions')
    @endif

    <table class="table">
        <thead>
            <tr>
                @foreach ( $fields as $field_name )
                    <th>{{ $controller->renderLabel($field_name) }}</th>
                @endforeach
                    <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(count($rows))
                @foreach ( $rows as $row )
                    <tr>
                        @foreach ( $fields as $field_name )
                            <td>{{ $controller->renderField('index', $row, $field_name, $row->$field_name) }}</td>
                        @endforeach
                        <td>
                            {{ Form::open(['route' => [strtolower($eloquent_model) . '.destroy', $row->id], 'method' => 'delete']) }}
                                <div class="btn-group">
                                    {{ link_to_route(strtolower($eloquent_model) . '.show', 'Show', [strtolower($eloquent_model) => $row->id], ['class' => 'btn btn-primary']) }}
                                    {{ link_to_route(strtolower($eloquent_model) . '.edit', 'Edit', [strtolower($eloquent_model) => $row->id], ['class' => 'btn btn-warning']) }}
                                    {{ Form::submit('Destroy', ['class' => 'btn btn-danger', 'onclick' => 'return confirm(\'Sure?\')']) }}
                                </div>
                            {{ Form::close() }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr >
                    <td colspan="{{count($fields)}}" align="center">No results</td>
                </tr>
            @endif

        </tbody>
    </table>

    @include('architect::partials.filters')
@endsection
