@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Balance Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Wallet Balance</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>XMR Balance: {{ number_format($balance, 12) }} XMR</h5>
                </div>
                <div class="col-md-6">
                    <h5>USD Value: ${{ number_format($usdAmount, 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposit/Withdrawal Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Deposit Monero</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('wallet.deposit-address') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Generate Deposit Address</button>
                    </form>
                    @if(session('deposit_address'))
                    <div class="mt-3">
                        <label class="form-label">Your deposit address:</label>
                        <input type="text" class="form-control" value="{{ session('deposit_address') }}" readonly>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Withdraw Monero</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('wallet.withdraw') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Monero Address</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (XMR)</label>
                            <input type="number" name="amount" class="form-control" step="0.000000000001" min="0.000000000001" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Withdraw</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Incoming Transactions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Pending Incoming Transactions</h5>
        </div>
        <div class="card-body">
            @if($pendingIncoming->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction Hash</th>
                                <th>Amount (XMR)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingIncoming as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td><code>{{ $transaction->tx_hash }}</code></td>
                                    <td>{{ number_format($transaction->amount, 12) }}</td>
                                    <td><span class="badge bg-warning">Awaiting Confirmation</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No pending incoming transactions detected yet</p>
            @endif
        </div>
    </div>

    <!-- Pending Outgoing Transactions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Pending Outgoing Transactions</h5>
        </div>
        <div class="card-body">
            @if($pendingOutgoing->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction Hash</th>
                                <th>Amount (XMR)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingOutgoing as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td><code>{{ $transaction->tx_hash }}</code></td>
                                    <td>{{ number_format($transaction->amount, 12) }}</td>
                                    <td><span class="badge bg-warning">Processing</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No pending outgoing transactions detected yet</p>
            @endif
        </div>
    </div>

    <!-- Confirmed Transactions -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Transaction History</h5>
                <form method="GET" action="{{ route('wallet.index') }}" class="form-inline">
                    <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('filter') == 'all' ? 'selected' : '' }}>All Transactions</option>
                        <option value="deposits" {{ request('filter') == 'deposits' ? 'selected' : '' }}>Deposits Only</option>
                        <option value="withdrawals" {{ request('filter') == 'withdrawals' ? 'selected' : '' }}>Withdrawals Only</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="card-body">
            @if($confirmedTransactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction Hash</th>
                                <th>Type</th>
                                <th>Amount (XMR)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($confirmedTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td><code>{{ $transaction->tx_hash }}</code></td>
                                    <td>
                                        @if($transaction->type === 'deposit')
                                            <span class="badge bg-success">Deposit</span>
                                        @else
                                            <span class="badge bg-primary">Withdrawal</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($transaction->amount, 12) }}</td>
                                    <td><span class="badge bg-success">Confirmed</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No confirmed transactions detected yet</p>
            @endif
        </div>
    </div>
</div>
@endsection