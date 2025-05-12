<nav class="space-y-2">
    <a href="{{ route('pelayan.dashboard') }}" 
        class="flex items-center p-3 rounded-lg 
        {{ request()->routeIs('pelayan.dashboard') ? 'bg-teal-600' : 'hover:bg-teal-600' }}">
        <i class="fas fa-home mr-3"></i>
        order
    </a>
    
    <a href="{{ route('pelayan.pesanan') }}" 
        class="flex items-center p-3 rounded-lg 
        {{ request()->routeIs('pelayan.pesanan') ? 'bg-teal-600' : 'hover:bg-teal-600' }}">
        <i class="fas fa-clipboard-list mr-3"></i>
        Reservasi Manager
    </a>
    

<a href="{{ route('pelayan.meja') }}" 
        class="flex items-center p-3 rounded-lg 
        {{ request()->routeIs('pelayan.meja') ? 'bg-teal-600' : 'hover:bg-teal-600' }}">
        <i class="fas fa-chair mr-3"></i>
        Table Management
    </a>

    <form method="POST" action="{{ route('logout') }}" class="w-full">
        @csrf
        <button type="submit" class="flex items-center w-full p-3 rounded-lg hover:bg-teal-600 text-white">
            <i class="fas fa-sign-out-alt mr-3"></i>
            Logout
        </button>
    </form>
</nav>