<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 12px;
            color: #222;
            padding: 20px;
        }

        
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #1a3c6e;
            margin-bottom: 16px;
            padding-bottom: 12px;
        }
        .kop-surat .nama-sekolah {
            font-size: 17px;
            font-weight: 700;
            color: #1a3c6e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-surat .sub-info {
            font-size: 11px;
            color: #444;
            margin-top: 3px;
        }
        .kop-surat .judul-laporan {
            font-size: 14px;
            font-weight: 700;
            color: #1a3c6e;
            margin-top: 10px;
            text-decoration: underline;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1a3c6e;
            margin-bottom: 16px;
            padding-bottom: 10px;
        }

        .header h2 {
            font-size: 16px;
            font-weight: 700;
            color: #1a3c6e;
        }

        .header h3 {
            font-size: 13px;
            font-weight: 600;
        }

        .header p {
            font-size: 11px;
            color: #555;
            margin-top: 4px;
        }

        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            table-layout: fixed;
        }

        th {
            background: #1a3c6e;
            color: #fff;
            padding: 7px 8px;
            text-align: left;
            font-size: 11px;
            word-wrap: break-word;
        }

        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
            word-wrap: break-word;
        }

        
        tr {
            page-break-inside: avoid;
        }

        tr:nth-child(even) td {
            background: #f4f7fb;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 11px;
            color: #888;
        }

        @media print {
            .no-print {
                display: none;
            }
            @page {
                size: landscape;
                margin: 15mm;
            }
        }

        .no-print {
            text-align: center;
            margin-bottom: 16px;
        }

        .no-print button {
            background: #1a3c6e;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="no-print"><button onclick="window.print()">🖨️ Cetak / Simpan PDF</button></div>

    
    <div class="kop-surat">
        <div class="nama-sekolah">{{ $sekolahNama ?? 'GARUDA AKADEMI' }}</div>
        <div class="sub-info">Tahun Ajaran {{ $tahunAjaran ?? '' }} | Semester {{ $semester ?? '' }}</div>
        <div class="sub-info">SISTEM MANAJEMEN PEMBELAJARAN BERBASIS DIGITAL</div>
        <div class="judul-laporan">{{ strtoupper($title ?? 'LAPORAN AKADEMIK') }}</div>
        <div class="sub-info" style="margin-top:6px; color:#888;">Dicetak: {{ $tanggalCetak ?? date('d F Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>{!! $colsHtml ?? '' !!}</tr>
        </thead>
        <tbody>{!! $rowsHtml ?? '' !!}</tbody>
    </table>
    <div class="footer">Dokumen ini diterbitkan oleh sistem GARA &mdash; rahasia</div>
</body>
<script>
    window.onload = function () { setTimeout(function () { window.print(); }, 600); };
</script>

</html>