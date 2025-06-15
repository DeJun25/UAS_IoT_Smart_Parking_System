<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Smart Parking</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
  <style>
    .chart-container {
      position: relative;
      height: 400px;
      width: 100%;
    }
  </style>
</head>
<body class="bg-gray-100">

  <div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Dashboard Monitoring Parking Slot</h1>
    
    <!-- Filter for the chart -->
    <div class="flex items-center space-x-3 mb-6">
      <label class="font-semibold">Tampilkan untuk Grafik:</label>
      <select id="filter" class="border rounded px-3 py-1">
        <option value="isi">Terisi</option>
        <option value="kosong">Kosong</option>
      </select>
    </div>
    
    <!-- Chart -->
    <div class="bg-white p-4 rounded shadow">
      <div class="chart-container">
        <canvas id="parkingChartCanvas"></canvas>
      </div>
    </div>

    <!-- Real-time Slot Status -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div id="slot1" class="bg-white p-6 rounded shadow text-center">
        <h2 class="text-xl font-bold mb-2">Slot 1</h2>
        <p id="slot1-status" class="text-2xl font-semibold">
          @if ($latestStatus['slot1'] === 1)
            Isi
          @elseif ($latestStatus['slot1'] === 0)
            Kosong
          @else
            Tidak Ada Data
          @endif
        </p>
        <div id="slot1-indicator" class="mt-2 mx-auto w-8 h-8 rounded-full 
          {{ $latestStatus['slot1'] === 1 ? 'bg-red-500' : ($latestStatus['slot1'] === 0 ? 'bg-green-500' : 'bg-gray-500') }}"></div>
      </div>
      <div id="slot2" class="bg-white p-6 rounded shadow text-center">
        <h2 class="text-xl font-bold mb-2">Slot 2</h2>
        <p id="slot2-status" class="text-2xl font-semibold">
          @if ($latestStatus['slot2'] === 1)
            Isi
          @elseif ($latestStatus['slot2'] === 0)
            Kosong
          @else
            Tidak Ada Data
          @endif
        </p>
        <div id="slot2-indicator" class="mt-2 mx-auto w-8 h-8 rounded-full 
          {{ $latestStatus['slot2'] === 1 ? 'bg-red-500' : ($latestStatus['slot2'] === 0 ? 'bg-green-500' : 'bg-gray-500') }}"></div>
      </div>
      <div id="slot3" class="bg-white p-6 rounded shadow text-center">
        <h2 class="text-xl font-bold mb-2">Slot 3</h2>
        <p id="slot3-status" class="text-2xl font-semibold">
          @if ($latestStatus['slot3'] === 1)
            Isi
          @elseif ($latestStatus['slot3'] === 0)
            Kosong
          @else
            Tidak Ada Data
          @endif
        </p>
        <div id="slot3-indicator" class="mt-2 mx-auto w-8 h-8 rounded-full 
          {{ $latestStatus['slot3'] === 1 ? 'bg-red-500' : ($latestStatus['slot3'] === 0 ? 'bg-green-500' : 'bg-gray-500') }}"></div>
      </div>
    </div>
  </div>

  <script>
    // Initialize variables
    let filter = 'isi';
    let chart;
    let labels = [];
    let dataSlots = { slot1: [], slot2: [], slot3: [] };

    // Fetch data for the chart
    function fetchDataForChart() {
      fetch(`/api/slots/daily-hours?status=${filter}`)
        .then(res => res.json())
        .then(json => {
          if (json && json.days && json.slots) {
            labels = json.days;
            dataSlots = json.slots;
            updateChart();
          }
        })
        .catch(err => console.error('Error fetching chart data:', err));
    }

    // Update the chart
    function updateChart() {
      chart.data.labels = labels;
      chart.data.datasets[0].data = dataSlots.slot1;
      chart.data.datasets[1].data = dataSlots.slot2;
      chart.data.datasets[2].data = dataSlots.slot3;
      chart.update();
    }

    // Update chart colors based on the filter (terisi or kosong)
    function updateChartColor() {
      const isFilled = filter === 'isi';
      chart.data.datasets[0].borderColor = isFilled ? '#F59E0B' : '#10B981';
      chart.data.datasets[1].borderColor = isFilled ? '#6366F1' : '#3B82F6';
      chart.data.datasets[2].borderColor = isFilled ? '#EF4444' : '#8B5CF6';
      chart.update();
    }

    // Event listener for the filter change
    document.getElementById('filter').addEventListener('change', function () {
      filter = this.value;
      fetchDataForChart();
      updateChartColor();
    });

    // Initialize the chart
    function init() {
      const ctx = document.getElementById('parkingChartCanvas');

      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: [],
          datasets: [
            {
              label: 'Slot 1',
              data: [],
              borderColor: '#F59E0B',
              backgroundColor: 'rgba(245, 158, 11, 0.1)',
              tension: 0.3,
              fill: true,
            },
            {
              label: 'Slot 2',
              data: [],
              borderColor: '#6366F1',
              backgroundColor: 'rgba(99, 102, 241, 0.1)',
              tension: 0.3,
              fill: true,
            },
            {
              label: 'Slot 3',
              data: [],
              borderColor: '#EF4444',
              backgroundColor: 'rgba(239, 68, 68, 0.1)',
              tension: 0.3,
              fill: true,
            },
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              title: {
                display: true,
                text: 'Hari',
                font: { weight: 'bold' }
              },
              grid: { display: false }
            },
            y: {
              min: 0,
              max: 24,
              title: {
                display: true,
                text: 'Jam (0-24)',
                font: { weight: 'bold' }
              },
              ticks: {
                stepSize: 6,
                callback: function (value) {
                  return value + ' jam';
                }
              }
            }
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: { font: { size: 14 } }
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  return context.dataset.label + ': ' + context.parsed.y + ' jam';
                }
              }
            }
          },
          interaction: {
            intersect: false,
            mode: 'index',
          },
          elements: {
            point: {
              radius: 5,
              hoverRadius: 8
            }
          }
        }
      });

      fetchDataForChart();

      // Polling data every 5 seconds
      setInterval(() => {
        fetchDataForChart();
      }, 5000);
    }

    // Initialize the chart when the page loads
    window.onload = init;
  </script>

</body>
</html>
