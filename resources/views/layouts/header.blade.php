<header class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">Marketplace</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile') }}">Profile</a>
                    </li>
                    @if(auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.categories.index') }}">Categories</a>
                    </li>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">Users</a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('settings') }}">Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('wallet.index') }}">
                            Wallet (XMR: {{ $xmrBalance }} ≈ ${{ number_format($usdBalance, 2) }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('messages.index') }}">
                            Messages
                            @if($unreadMessagesCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $unreadMessagesCount }}
                                    <span class="visually-hidden">unread messages</span>
                                </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</header>
