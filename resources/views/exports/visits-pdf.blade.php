<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #111; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        p.meta { color: #666; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        th { background: #f3f4f6; }
        tr:nth-child(even) { background: #fafafa; }
    </style>
</head>
<body>
    <h1>VisitaTrack — Reporte de visitas</h1>
    <p class="meta">Generado el {{ now()->format('d/m/Y H:i') }} — {{ $rows->count() }} visita(s)</p>

    <table>
        <thead>
            <tr>
                <th>Trabajador</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Empresa / Máquina</th>
                <th>N° OV</th>
                <th>N° OT</th>
                <th>Salida base</th>
                <th>Llegada destino</th>
                <th>Salida destino</th>
                <th>Llegada base</th>
                <th>Km</th>
                <th>Min. en destino</th>
                <th>Min. totales</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['worker'] }}</td>
                    <td>{{ $row['type'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['company_or_machine'] }}</td>
                    <td>{{ $row['ov_number'] }}</td>
                    <td>{{ $row['ot_number'] }}</td>
                    <td>{{ $row['departed_base_at'] }}</td>
                    <td>{{ $row['arrived_client_at'] }}</td>
                    <td>{{ $row['departed_client_at'] }}</td>
                    <td>{{ $row['arrived_base_at'] }}</td>
                    <td>{{ $row['distance_km'] }}</td>
                    <td>{{ $row['duration_at_site_min'] }}</td>
                    <td>{{ $row['total_trip_min'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
