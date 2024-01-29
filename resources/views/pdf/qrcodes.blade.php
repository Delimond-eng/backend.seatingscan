<!DOCTYPE html>
<html lang="fr ">
<head>
    <title>Seating scan(Guests Qrcodes)</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px;
            text-align: center;
            vertical-align: middle;
            margin: 0;
        }

        img {
            width: 50px;
            height: 50px;
            display: block;
            margin: 0 auto;
        }

        p {
            font-family: 'Arial', sans-serif;
            font-size: 5px;
            margin-top:1px;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
<table>
    @foreach($qrcodes as $index => $qrcode)
    @if($index % 12 === 0)
    </tr>
    @endif

    @if($index % 12 === 0)
        <tr>
            @endif

            <td>
                <img src="{!! $qrcode['invite_qrcode'] !!}" alt="QR Code">
                <p>{{ $qrcode['invite_nom'] }}</p>
            </td>

            @if(($index + 1) % 12 === 0 || $index === count($qrcodes) - 1)
        </tr>
    @endif
    @endforeach
</table>
</body>
</html>
