<x-layout>
  <x-slot:title>{{ $title ?? 'Dashboard' }}</x-slot:title>

  <div class="p-6 space-y-6">
    {{-- Header Section --}}
    <div class="text-2xl font-bold text-gray-800">Report</div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white shadow rounded-lg p-4">
        <div class="text-sm text-gray-500">Total Reservation</div>
        <div class="text-2xl font-semibold text-blue-600">{{ $totalReservations }}</div>
      </div>
      <div class="bg-white shadow rounded-lg p-4">
        <div class="text-sm text-gray-500">Best Selling Item</div>
        <div class="text-2xl font-semibold text-purple-600">{{ $bestSellingItem ?? 'No Data' }}</div>
      </div>
      <div class="bg-white shadow rounded-lg p-4">
        <div class="text-sm text-gray-500">Present</div>
        <div class="text-2xl font-semibold text-green-600">{{ $presentReservations }}</div>
      </div>
      <div class="bg-white shadow rounded-lg p-4">
        <div class="text-sm text-gray-500">Total Orders</div>
        <div class="text-2xl font-semibold text-indigo-600">{{ $totalOrders }}</div>
      </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Pie Chart --}}
      <div class="bg-white shadow rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
          <div class="font-semibold">Customer Attendance Graph</div>
          <select class="border border-gray-300 text-sm rounded p-1" disabled>
            <option>6 months</option>
          </select>
        </div>
        <canvas id="attendanceChart" height="200"></canvas>
      </div>

      {{-- Bar Chart --}}
      <div class="bg-white shadow rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
          <div class="font-semibold">Transaction Graph</div>
          <select class="border border-gray-300 text-sm rounded p-1" disabled>
            <option>Last 30 Days</option>
          </select>
        </div>
        <canvas id="transactionChart" height="200"></canvas>
      </div>
    </div>

    {{-- Staff Table --}}
    <div class="bg-white shadow rounded-lg p-4">
      <div class="flex justify-between items-center mb-4">
        <div class="font-semibold">Staff Performance Reports</div>
        <div class="flex gap-2">
          <input type="text" placeholder="Search" class="border border-gray-300 rounded p-1 text-sm" />
          <button class="bg-gray-100 border rounded px-2 py-1 text-sm">Filter</button>
          <button class="bg-blue-600 text-white rounded px-2 py-1 text-sm">Export</button>
        </div>
      </div>
      <table class="w-full text-sm text-left">
        <thead>
          <tr class="text-gray-700 border-b">
            <th class="py-2">Nama</th>
            <th class="py-2">Peran</th>
            <th class="py-2">Jumlah Reservasi</th>
            <th class="py-2">Rata-rata Rating</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($staffPerformance as $staff)
            <tr class="border-b">
              <td class="py-2">{{ $staff->nama }}</td>
              <td class="py-2">{{ $staff->peran }}</td>
              <td class="py-2">{{ $staff->jumlah_reservasi }}</td>
              <td class="py-2">{{ $staff->rata_rata_rating ?? 'Belum ada rating' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="py-2 text-center">Tidak ada data staf.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      <table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Peran</th>
            <th>Jumlah Reservasi</th>
            <th>Rata-rata Rating</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($staffPerformance as $staff)
            <tr>
                <td>{{ $staff->nama }}</td>
                <td>{{ $staff->peran }}</td>
                <td>{{ $staff->jumlah_reservasi }}</td>
                <td>{{ $staff->rata_rata_rating ?? 'Belum ada rating' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Tidak ada data staf.</td>
            </tr>
        @endforelse
    </tbody>
</table>

    </div>
  </div>

  {{-- Chart.js --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(attendanceCtx, {
      type: 'doughnut',
      data: {
        labels: ['Total Reservation', 'Present', 'Total Order'],
        datasets: [{
          data: [{{ $totalReservations }}, {{ $presentReservations }}, {{ $totalOrders }}],
          backgroundColor: ['#f97316', '#10b981', '#8b5cf6'],
        }]
      },
      options: {
        responsive: true,
        cutout: '70%'
      }
    });

    const transactionCtx = document.getElementById('transactionChart').getContext('2d');
    const transactionChart = new Chart(transactionCtx, {
      type: 'bar',
      data: {
        labels: {!! json_encode($transactionLabels) !!},
        datasets: [{
          label: 'Total Transaksi (Rp)',
          data: {!! json_encode($transactionData) !!},
          backgroundColor: '#3b82f6'
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
</x-layout>
