@extends('layouts.print')

@section(
'title',
'Slip Gaji '
. $penggajian->pegawai->nip
. ' - PayrollHub'
)

@section('orientasi', 'portrait')

@section('content')
@include('penggajian._slip-cetak')
@endsection