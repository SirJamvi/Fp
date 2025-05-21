<x-layout>
  <x-slot:title>{{ $title ?? 'Dashboard' }}</x-slot:title>

  <div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="text-2xl font-bold text-gray-800">Report</div>

    {{-- Stat Cards --}}
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

    {{-- Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Customer Attendance Chart --}}
      <div class="bg-white shadow rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
          <div class="font-semibold">Customer Attendance Graph</div>
          <select id="attendanceFilter" class="border border-gray-300 text-sm rounded p-1">
            <option value="today">Hari Ini</option>
            <option value="week">Minggu Ini</option>
            <option value="month" selected>Bulan Ini</option>
            <option value="year">Tahun Ini</option>
          </select>
        </div>
        <canvas id="attendanceChart" height="200"></canvas>
      </div>

      {{-- Transaction Chart --}}
      <div class="bg-white shadow rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
          <div class="font-semibold">Transaction Graph</div>
          <select id="transactionFilter" class="border border-gray-300 text-sm rounded p-1">
            <option value="today">Hari Ini</option>
            <option value="week">Minggu Ini</option>
            <option value="month" selected>Bulan Ini</option>
            <option value="year">Tahun Ini</option>
          </select>
        </div>
        <canvas id="transactionChart" height="200"></canvas>
      </div>
    </div>

    {{-- Staff Performance --}}
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
    </div>
  </div>

  {{-- ChartJS --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    // Initial data from controller
    const attendanceData = {
      today: {{ Js::from($attendanceChart['today']) }},
      week: {{ Js::from($attendanceChart['week']) }},
      month: {{ Js::from($attendanceChart['month']) }},
      year: {{ Js::from($attendanceChart['year']) }},
    };

    const transactionData = {
      today: {
        labels: {!! json_encode($transactionChart['today']['labels']) !!},
        data: {!! json_encode($transactionChart['today']['data']) !!}
      },
      week: {
        labels: {!! json_encode($transactionChart['week']['labels']) !!},
        data: {!! json_encode($transactionChart['week']['data']) !!}
      },
      month: {
        labels: {!! json_encode($transactionChart['month']['labels']) !!},
        data: {!! json_encode($transactionChart['month']['data']) !!}
      },
      year: {
        labels: {!! json_encode($transactionChart['year']['labels']) !!},
        data: {!! json_encode($transactionChart['year']['data']) !!}
      },
    };

    // Chart: Attendance
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    let attendanceChart = new Chart(attendanceCtx, {
      type: 'doughnut',
      data: {
        labels: ['Total Reservation', 'Present', 'Total Order'],
        datasets: [{
          data: attendanceData['month'],
          backgroundColor: ['#f97316', '#10b981', '#8b5cf6'],
        }]
      },
      options: {
        responsive: true,
        cutout: '70%'
      }
    });

    document.getElementById('attendanceFilter').addEventListener('change', function () {
      const filter = this.value;
      attendanceChart.data.datasets[0].data = attendanceData[filter];
      attendanceChart.update();
    });

    // Chart: Transaction
    const transactionCtx = document.getElementById('transactionChart').getContext('2d');
    let transactionChart = new Chart(transactionCtx, {
      type: 'bar',
      data: {
        labels: transactionData['month'].labels,
        datasets: [{
          label: 'Total Transaksi (Rp)',
          data: transactionData['month'].data,
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

    document.getElementById('transactionFilter').addEventListener('change', function () {
      const filter = this.value;
      transactionChart.data.labels = transactionData[filter].labels;
      transactionChart.data.datasets[0].data = transactionData[filter].data;
      transactionChart.update();
    });
  </script>
</x-layout>
