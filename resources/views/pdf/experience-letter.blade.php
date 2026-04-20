@component('pdf.layout')
@slot('title')Surat Pengalaman Kerja@endslot

<p>Yang bertanda tangan di bawah ini, menyatakan bahwa:</p>
<ul>
    <li>Nama: <strong>{{ $employee->user->name }}</strong></li>
    <li>Jabatan terakhir: <strong>{{ $employee->position }}</strong></li>
    <li>Departemen: <strong>{{ $employee->department }}</strong></li>
    <li>Periode bekerja: <strong>{{ optional($employee->hire_date)->format('d M Y') }} s/d {{ optional($employee->termination_date)->format('d M Y') ?? 'sekarang' }}</strong></li>
</ul>

<p>Adalah benar pernah bekerja di perusahaan kami dengan kinerja baik dan dedikasi tinggi.</p>
<p>Surat ini dibuat untuk digunakan sebagaimana mestinya.</p>

<br><br>
<div style="text-align:right;">
    <span>{{ $date }}</span><br>
    <span>HR Department</span>
</div>
@endcomponent
