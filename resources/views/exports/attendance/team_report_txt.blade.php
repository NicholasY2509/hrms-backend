RINGKASAN KEHADIRAN PER TIM
Periode: {{ $filters['start_date'] ?? '-' }} s/d {{ $filters['end_date'] ?? '-' }}
Dicetak pada: {{ now()->format('d/m/Y H:i') }}
------------------------------------------------------------

@php
    $statuses = ['Terlambat', 'Izin', 'Absen', 'Sakit', 'Cuti', 'Training', 'Dinas_Luar_Kota'];
@endphp

[RINGKASAN KEHADIRAN]
@foreach($data as $row)

{{ $row->group_name }} : {{ $row->headcount }} orang
@foreach($statuses as $status)
{{ str_pad(str_replace('_', ' ', $status), 18) }} : {{ $row->$status }}
@endforeach
{{ str_pad('Hadir', 18) }} : {{ $row->Hadir }}

@foreach($statuses as $status)
@php $listProp = 'list_' . $status; @endphp
@if(!empty($row->$listProp))
List {{ str_replace('_', ' ', $status) }}:
{!! $row->$listProp !!}

@endif
@endforeach
@endforeach
