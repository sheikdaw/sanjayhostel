{{-- resources/views/admin/advances/monthly.blade.php --}}
@extends('layouts.office')

@section('title', 'Monthly Advance Report')
@section('page_title', 'Monthly Advance Report')

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Monthly Advance Report</h1>
        <p class="ol-page-sub">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</p>
    </div>
    <div>
        <a href="{{ route('admin.advances.index') }}" class="rv-submit" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="ds-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Code</th>
                    <th>Advance Taken</th>
                    <th>Deducted</th>
                    <th>Balance</th>
                    <th>Salary</th>
                    <th>Net Salary</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $index => $data)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $data['employee']->name }}</strong></td>
                        <td>{{ $data['employee']->employee_code }}</td>
                        <td class="text-warning">₹{{ number_format($data['advance_taken'], 2) }}</td>
                        <td class="text-success">₹{{ number_format($data['advance_deducted'], 2) }}</td>
                        <td class="text-{{ $data['advance_balance'] > 0 ? 'danger' : 'success' }}">
                            ₹{{ number_format($data['advance_balance'], 2) }}
                        </td>
                        <td>₹{{ number_format($data['salary'], 2) }}</td>
                        <td><strong>₹{{ number_format($data['net_salary'], 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">No data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection