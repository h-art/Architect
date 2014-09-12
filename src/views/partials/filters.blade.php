@if(count($filters))
    {{ Form::open(['route' => [strtolower($eloquent_model) . '.filter'], 'method' => 'POST']) }}
        <table class="table">
        <thead>
            <tr>
                <th colspan="2">Filters</th>
            </tr>
        </thead>
        <tbody>
            @foreach ( $filters as $field_name => $filter )
                <tr>
                    <td>{{ $controller->renderLabel($field_name) }}</td>
                    <td>{{ $filter }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">
                    {{ Form::submit('Search', ['class' => 'btn btn-primary']) }}
                </td>
            </tr>

        </tbody>
    </table>
    {{ Form::close() }}
@endif