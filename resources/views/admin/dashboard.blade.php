<x-layout>
  <x-slot:title>{{ $title ?? 'Dashboard' }}</x-slot:title>

  <div class="p-6 space-y-6">
    {{-- Header Section --}}
    <div class="text-2xl font-bold text-gray-800">Report</div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white shadow rounded-lg p-4">
        <div class="text-sm text-gray-500">Total Reservation</div>
        <div class="text-2xl font-semibold text-blue-600">100</div>
      </div>
      <div class="bg-white shadow rounded-lg p-4">
        <div class="text-sm text-gray-500">Best Selling Item</div>
        <div class="text-2xl font-semibold text-purple-600">Paha Atas</div>
      </div>
      <div class="bg-white shadow rounded-lg p-4">
        <div class="text-sm text-gray-500">Present</div>
        <div class="text-2xl font-semibold text-green-600">80</div>
      </div>
      <div class="bg-white shadow rounded-lg p-4">
        <div class="text-sm text-gray-500">Total Orders</div>
        <div class="text-2xl font-semibold text-indigo-600">90</div>
      </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Pie Chart --}}
      <div class="bg-white shadow rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
          <div class="font-semibold">Customer Attendance Graph</div>
          <select class="border border-gray-300 text-sm rounded p-1">
            <option>6 months</option>
          </select>
        </div>
        <canvas id="attendanceChart" height="200"></canvas>
      </div>

      {{-- Bar Chart --}}
      <div class="bg-white shadow rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
          <div class="font-semibold">Transaction Graph</div>
          <select class="border border-gray-300 text-sm rounded p-1">
            <option>1 month</option>
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
      <table class="w-full text-sm text-left text-gray-700">
        <thead class="text-xs uppercase bg-gray-100">
          <tr>
            <th scope="col" class="px-4 py-2">Name</th>
            <th scope="col" class="px-4 py-2">Role</th>
            <th scope="col" class="px-4 py-2">Reservation Served</th>
            <th scope="col" class="px-4 py-2">Ratings</th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-b">
            <td class="px-4 py-2">Adi Rizki</td>
            <td class="px-4 py-2">Waiter</td>
            <td class="px-4 py-2">60 Orang</td>
            <td class="px-4 py-2">⭐ 4.8</td>
          </tr>
          <tr class="border-b">
            <td class="px-4 py-2">Rahmat</td>
            <td class="px-4 py-2">Chef</td>
            <td class="px-4 py-2">90 Orang</td>
            <td class="px-4 py-2">⭐ 5.0</td>
          </tr>
          <tr class="border-b">
            <td class="px-4 py-2">Cici</td>
            <td class="px-4 py-2">Waiter</td>
            <td class="px-4 py-2">30 Orang</td>
            <td class="px-4 py-2">⭐ 4.5</td>
          </tr>
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
          data: [100, 80, 90],
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
        labels: ['Paha Atas', 'Dada', 'Sayap', 'Kulit', 'Nasi', 'Paha Bawah'],
        datasets: [{
          label: 'Transaksi',
          data: [19900, 17250, 14650, 11250, 8850, 6600],
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