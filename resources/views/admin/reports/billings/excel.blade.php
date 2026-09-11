<table>
    <thead>
        <tr>
            <th colspan="{{ count($headings) }}">{{ $title }}</th>
        </tr>
        <tr>
            @foreach ($headings as $heading)
                <th>{{ $heading }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                @foreach ($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($headings) }}">Sin registros</td>
            </tr>
        @endforelse
    </tbody>
</table>
