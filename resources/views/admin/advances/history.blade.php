{{-- resources/views/admin/advances/history.blade.php --}}
@extends('layouts.office')

@section('title', 'Advance History')
@section('page_title', 'Advance History')

@section('content')

<div class="ol-page-header">
    <div>
        <h1 class="ol-page-title">Advance History</h1>
        <p class="ol-page-sub">{{ $employee->name }} ({{ $employee->employee_code }})</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.advances.index') }}" class="rv-submit" style="width:auto; height:38px; padding:0 1.2rem; font-size:0.8rem !important; border-radius:9px !important; display:inline-flex; align-items:center; gap:6px; animation:none; background:#6b7280;">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

{{-- Summary Cards --}}
<div class="employee-stats" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px,1fr)); gap:1rem; margin-bottom:1.5rem;">
    <div class="employee-stat total">
        <div class="number">₹{{ number_format($employee->advance_amount, 2) }}</div>
        <div class="label">Total Advance</div>
    </div>
    <div class="employee-stat active">
        <div class="number" style="color:#22c55e;">₹{{ number_format($employee->advance_deduct, 2) }}</div>
        <div class="label">Total Deducted</div>
    </div>
    <div class="employee-stat salary">
        <div class="number" style="color: {{ $employee->advance_balance > 0 ? '#dc2626' : '#22c55e' }};">
            ₹{{ number_format($employee->advance_balance, 2) }}
        </div>
        <div class="label">Outstanding Balance</div>
    </div>
</div>

{{-- Month Filter --}}
<div class="filter-section" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center; background:white; padding:0.75rem 1rem; border-radius:10px; border:1px solid #e5e7eb; margin-bottom:1.5rem;">
    <div class="filter-group">
        <label style="font-size:0.8rem; font-weight:600;">Select Month:</label>
    </div>
    <div class="filter-group">
        <select id="filterMonth" onchange="window.location.href='{{ route('admin.advances.history', $employee->id) }}?month='+this.value">
            @foreach($availableMonths as $m)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('F Y') }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- Transactions Table --}}
<div class="ds-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Deducted</th>
                    <th>Balance After</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ date('d M Y', strtotime($transaction->transaction_date)) }}</td>
                        <td>
                            <span class="badge bg-{{ $transaction->transaction_type == 'advance' ? 'warning' : 'info' }}">
                                {{ $transaction->type_label }}
                            </span>
                        </td>
                        <td class="text-warning">₹{{ number_format($transaction->amount, 2) }}</td>
                        <td class="text-info">₹{{ number_format($transaction->deducted_amount, 2) }}</td>
                        <td class="text-{{ ($transaction->amount - $transaction->deducted_amount) > 0 ? 'danger' : 'success' }}">
                            ₹{{ number_format($transaction->amount - $transaction->deducted_amount, 2) }}
                        </td>
                        <td>{{ $transaction->remarks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No transactions found for this month.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection