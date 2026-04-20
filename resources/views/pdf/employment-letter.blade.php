@component('pdf.layout')
@slot('title')Surat Keterangan Bekerja@endslot

<p>Dengan ini kami menyatakan bahwa:</p>
<ul>
    <li>Nama: <strong>{{ $employee->user->name }}</strong></li>
    <li>Jabatan: <strong>{{ $employee->position }}</strong></li>
    <li>Departemen: <strong>{{ $employee->department }}</strong></li>
    <li>Mulai bekerja: <strong>{{ optional($employee->hire_date)->format('d M Y') }}</strong></li>
</ul>

<p>Saat ini masih aktif sebagai karyawan di perusahaan kami.</p>
<p>Surat ini dibuat untuk keperluan administrasi dan dapat digunakan sebagaimana mestinya.</p>

<br><br>
<div style="text-align:right;">
    <span>{{ $date }}</span><br>
    <span>HR Department</span>
</div>
@endcomponent
