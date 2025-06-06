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
            <option value="today" {{ ($filter ?? 'month') === 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="week" {{ ($filter ?? 'month') === 'week' ? 'selected' : '' }}>Minggu Ini</option>
            <option value="month" {{ ($filter ?? 'month') === 'month' ? 'selected' : '' }}>Bulan Ini</option>
            <option value="year" {{ ($filter ?? 'month') === 'year' ? 'selected' : '' }}>Tahun Ini</option>
          </select>
        </div>
        <canvas id="attendanceChart" height="200"></canvas>
      </div>

      {{-- Transaction Chart --}}
      <div class="bg-white shadow rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
          <div class="font-semibold">Transaction Graph</div>
          <select id="transactionFilter" class="border border-gray-300 text-sm rounded p-1">
            <option value="today" {{ ($filter ?? 'month') === 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="week" {{ ($filter ?? 'month') === 'week' ? 'selected' : '' }}>Minggu Ini</option>
            <option value="month" {{ ($filter ?? 'month') === 'month' ? 'selected' : '' }}>Bulan Ini</option>
            <option value="year" {{ ($filter ?? 'month') === 'year' ? 'selected' : '' }}>Tahun Ini</option>
          </select>
        </div>
        <canvas id="transactionChart" height="200"></canvas>
      </div>
    </div>

    {{-- Ratings Section --}}
    <div class="bg-white shadow rounded-lg p-4">
      <div class="flex justify-between items-center mb-4">
        <div class="font-semibold">Ratings</div>
        <div class="flex gap-2 items-center">
          <select id="ratingFilter" class="border border-gray-300 rounded p-1 text-sm">
            <option value="today" {{ ($filter ?? 'week') === 'today' ? 'selected' : '' }}>Hari Ini</option>
            <option value="week" {{ ($filter ?? 'week') === 'week' ? 'selected' : '' }}>Minggu Ini</option>
            <option value="month" {{ ($filter ?? 'week') === 'month' ? 'selected' : '' }}>Bulan Ini</option>
            <option value="year" {{ ($filter ?? 'week') === 'year' ? 'selected' : '' }}>Tahun Ini</option>
          </select>

          <button id="filterBtn" class="bg-gray-100 border rounded px-2 py-1 text-sm">Filter</button>

          <form method="GET" action="{{ route('admin.ratings.exportPdf') }}" target="_blank" class="m-0 p-0">
            <input type="hidden" name="filter" id="exportFilter" value="{{ $filter ?? 'week' }}" />
            <button type="submit" class="bg-blue-600 text-white rounded px-2 py-1 text-sm">Export PDF</button>
          </form>
        </div>
      </div>

      <table class="w-full text-sm text-left">
        <thead>
          <tr class="text-gray-700 border-b">
            <th class="py-2">Kategori</th>
            <th class="py-2">Rating</th>
            <th class="py-2">Komentar</th>
            <th class="py-2">Pengguna</th>
          </tr>
        </thead>
        <tbody>
          @forelse($ratings as $rating)
            <tr class="border-b">
              <td class="py-2 font-medium">Makanan</td>
              <td class="py-2">
                @for($i = 1; $i <= 5; $i++)
                  <span class="text-yellow-400">
                    {{ $i <= $rating->food_rating ? '★' : '☆' }}
                  </span>
                @endfor
                ({{ $rating->food_rating }})
              </td>
              <td class="py-2" rowspan="3">{{ $rating->comment }}</td>
              <td class="py-2" rowspan="3">
                {{ $rating->pengguna->nama ?? 'Anonim' }}<br>
                <span class="text-xs text-gray-500">
                  {{ optional($rating->created_at)->format('d M Y') ?? 'Tanggal tidak tersedia' }}
                </span>
              </td>
            </tr>
            <tr class="border-b">
              <td class="py-2 font-medium">Pelayanan</td>
              <td class="py-2">
                @for($i = 1; $i <= 5; $i++)
                  <span class="text-yellow-400">
                    {{ $i <= $rating->service_rating ? '★' : '☆' }}
                  </span>
                @endfor
                ({{ $rating->service_rating }})
              </td>
            </tr>
            <tr class="border-b">
              <td class="py-2 font-medium">Aplikasi</td>
              <td class="py-2">
                @for($i = 1; $i <= 5; $i++)
                  <span class="text-yellow-400">
                    {{ $i <= $rating->app_rating ? '★' : '☆' }}
                  </span>
                @endfor
                ({{ $rating->app_rating }})
              </td>
            </tr>
            <tr><td colspan="4" class="h-4 bg-gray-50"></td></tr>
          @empty
            <tr>
              <td colspan="4" class="py-4 text-center">Belum ada rating yang diterima</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ChartJS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
      // Data dari controller
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

      // Inisialisasi Chart Attendance
      const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
      let attendanceChart = new Chart(attendanceCtx, {
        type: 'doughnut',
        data: {
          labels: ['Total Reservation', 'Present', 'Total Order'],
          datasets: [{
            data: attendanceData['{{ $filter ?? 'month' }}'],
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

      // Inisialisasi Chart Transaction
      const transactionCtx = document.getElementById('transactionChart').getContext('2d');
      let transactionChart = new Chart(transactionCtx, {
        type: 'bar',
        data: {
          labels: transactionData['{{ $filter ?? 'month' }}'].labels,
          datasets: [{
            label: 'Total Transaksi (Rp)',
            data: transactionData['{{ $filter ?? 'month' }}'].data,
            backgroundColor: '#3b82f6'
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: { beginAtZero: true }
          }
        }
      });

      document.getElementById('transactionFilter').addEventListener('change', function () {
        const filter = this.value;
        transactionChart.data.labels = transactionData[filter].labels;
        transactionChart.data.datasets[0].data = transactionData[filter].data;
        transactionChart.update();
      });

      // Filter & Export PDF control untuk rating
      document.getElementById('filterBtn').addEventListener('click', () => {
        const filterValue = document.getElementById('ratingFilter').value;
        document.getElementById('exportFilter').value = filterValue;

        // Reload halaman dengan query param filter
        const url = new URL(window.location.href);
        url.searchParams.set('filter', filterValue);
        window.location.href = url.toString();
      });
    </script>
  </div>
</x-layout>
