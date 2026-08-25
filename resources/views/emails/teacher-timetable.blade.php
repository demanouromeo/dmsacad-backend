<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; }
    h2 { margin-bottom: 4px; }
    p.meta { color: #6b7280; margin-top: 0; }
    table { border-collapse: collapse; width: 100%; margin-top: 12px; }
    th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; font-size: 13px; vertical-align: top; }
    th { background: #f3f4f6; }
    td.time { white-space: nowrap; font-weight: bold; }
    td.time span { display: block; font-weight: normal; font-size: 11px; color: #6b7280; }
    td.empty { color: #9ca3af; text-align: center; }
    .subject { font-weight: bold; }
    .classe { font-size: 11px; color: #6b7280; }
</style>
</head>
<body>
    <h2>Emploi du temps - {{ $teacherName }}</h2>
    <p class="meta">Année scolaire : {{ $schoolYear }}</p>
    <table>
        <thead>
            <tr>
                <th></th>
                @foreach ($days as $day)
                    <th>{{ $day['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($periods as $period)
                <tr>
                    <td class="time">
                        {{ $period['number'] }}
                        @if ($period['start'])
                            <span>{{ $period['start'] }} - {{ $period['end'] }}</span>
                        @endif
                    </td>
                    @foreach ($days as $day)
                        @php $cell = $grid[$period['number']][$day['jour_id']] ?? null; @endphp
                        @if ($period['number'] > $day['number_of_periods'])
                            <td class="empty">&mdash;</td>
                        @elseif ($cell)
                            <td>
                                <div class="subject">{{ $cell['subject_title'] }}</div>
                                <div class="classe">{{ $cell['classe_name'] }}</div>
                            </td>
                        @else
                            <td class="empty">&mdash;</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
