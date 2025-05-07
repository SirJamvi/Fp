<nav class="space-y-2">
    <a href="{{ route('pelayan.dashboard') }}" 
        class="flex items-center p-3 rounded-lg 
        {{ request()->routeIs('pelayan.dashboard') ? 'bg-teal-600' : 'hover:bg-teal-600' }}">
        <i class="fas fa-home mr-3"></i>
        Dashboard
    </a>
    
    <a href="{{ route('pelayan.pesanan') }}" 
        class="flex items-center p-3 rounded-lg 
        {{ request()->routeIs('pelayan.pesanan') ? 'bg-teal-600' : 'hover:bg-teal-600' }}">
        <i class="fas fa-clipboard-list mr-3"></i>
        Kelola Pesanan
    </a>
    
    <a href="{{ route('pelayan.meja') }}" 
        class="flex items-center p-3 rounded-lg 
        {{ request()->routeIs('pelayan.meja') ? 'bg-teal-600' : 'hover:bg-teal-600' }}">
        <i class="fas fa-chair mr-3"></i>
        Status Meja
    </a>
</nav>