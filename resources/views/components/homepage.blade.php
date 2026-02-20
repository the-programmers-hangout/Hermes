@php
  $placeholderImage = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"><rect width="48" height="48" rx="8" fill="#1e1f22"/><text x="24" y="28" text-anchor="middle" font-size="18" fill="#b5bac1">?</text></svg>');

  $emoteImageSrc = function ($image) use ($placeholderImage) {
    if (empty($image)) {
      return $placeholderImage;
    }

    $value = (string) $image;

    if (str_starts_with($value, 'data:image/')) {
      return $value;
    }

    $binary = base64_decode($value, true);

    if ($binary === false || $binary === '') {
      return $placeholderImage;
    }

    $mime = 'image/png';

    if (str_starts_with($binary, 'GIF8')) {
      $mime = 'image/gif';
    } elseif (substr($binary, 0, 2) === "\xFF\xD8") {
      $mime = 'image/jpeg';
    }

    return 'data:'.$mime.';base64,'.base64_encode($binary);
  };

  $chartPayload = [
    'usage_over_time' => [
      'labels' => data_get($stats, 'usage_over_time.labels', []),
      'values' => data_get($stats, 'usage_over_time.values', []),
    ],
  ];

  $customUsageTables = [
    [
      'title' => 'Top 10 Static Emotes',
      'list' => $stats['top_static'] ?? collect(),
      'empty' => 'No static emote usage found.',
    ],
    [
      'title' => 'Top 10 Animated Emotes',
      'list' => $stats['top_animated'] ?? collect(),
      'empty' => 'No animated emote usage found.',
    ],
    [
      'title' => 'Bottom 10 Static Emotes',
      'list' => $stats['bottom_static'] ?? collect(),
      'empty' => 'No static emote usage found.',
    ],
    [
      'title' => 'Bottom 10 Animated Emotes',
      'list' => $stats['bottom_animated'] ?? collect(),
      'empty' => 'No animated emote usage found.',
    ],
  ];
@endphp

<x-layouts.app>
<div class="w-full max-w-7xl px-4 py-8 mx-auto sm:px-6 lg:px-8 lg:py-10">
  <h1 class="mb-8 text-3xl font-semibold text-white">Emoji Stats</h1>

    <div class="grid gap-4 mb-8">
      <label for="stats-user-id" class="text-sm text-[#b5bac1]">Filter by User ID (optional)</label>
      <form method="GET" action="/" class="flex flex-col gap-2 sm:flex-row">
        <input
          id="stats-user-id"
          type="text"
          name="user_id"
          value="{{ $userIdInput ?? '' }}"
          class="w-full px-4 py-3 border rounded-md bg-[#1e1f22] border-[#3f4147] text-[#dbdee1] placeholder-[#7f838b] focus:outline-none focus:ring-2 focus:ring-[#5865f2]"
          placeholder="Enter Discord user ID..."
        />
        <button type="submit" class="px-5 py-3 font-medium text-white rounded-md bg-[#5865f2] hover:bg-[#4752c4]">Apply</button>
        <a href="/" class="px-5 py-3 rounded-md border border-[#3f4147] bg-[#2b2d31] text-[#dbdee1] hover:bg-[#35373c]">Clear</a>
      </form>

      @if (!empty($validationErrors['user_id']))
        <div class="mt-1 text-[#f23f43]">{{ $validationErrors['user_id'] }}</div>
      @endif

      @if (!empty($stats['is_filtered']))
        <p class="text-sm text-[#b5bac1]">
          Showing stats for user ID: <strong>{{ $stats['filtered_user_id'] }}</strong>
        </p>
      @else
        <p class="text-sm text-[#b5bac1]">Showing stats for all users.</p>
      @endif
    </div>

    <div class="mb-8">
      <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <div class="p-4 border rounded-lg bg-[#2b2d31] border-[#3f4147] min-w-0">
          <p class="text-xs font-medium uppercase tracking-wide text-[#b5bac1]">Total Emoji Usage</p>
          <p class="mt-2 text-2xl font-semibold text-white break-words">{{ $stats['total_usage'] ?? 0 }}</p>
        </div>

        <div class="p-4 border rounded-lg bg-[#2b2d31] border-[#3f4147] min-w-0">
          <p class="text-xs font-medium uppercase tracking-wide text-[#b5bac1]">Unique Emotes</p>
          <p class="mt-2 text-2xl font-semibold text-white break-words">{{ $stats['unique_emotes'] ?? 0 }}</p>
        </div>

        <div class="p-4 border rounded-lg bg-[#2b2d31] border-[#3f4147] min-w-0">
          <p class="text-xs font-medium uppercase tracking-wide text-[#b5bac1]">Users Included</p>
          <p class="mt-2 text-2xl font-semibold text-white break-words">{{ $stats['unique_users'] ?? 0 }}</p>
        </div>

        <div class="p-4 border rounded-lg bg-[#2b2d31] border-[#3f4147] min-w-0">
          <p class="text-xs font-medium uppercase tracking-wide text-[#b5bac1]">Static Usage</p>
          <p class="mt-2 text-2xl font-semibold text-white break-words">{{ $stats['usage_by_type']['STATIC'] ?? 0 }}</p>
        </div>

        <div class="p-4 border rounded-lg bg-[#2b2d31] border-[#3f4147] min-w-0">
          <p class="text-xs font-medium uppercase tracking-wide text-[#b5bac1]">Animated Usage</p>
          <p class="mt-2 text-2xl font-semibold text-white break-words">{{ $stats['usage_by_type']['ANIMATED'] ?? 0 }}</p>
        </div>

        <div class="p-4 border rounded-lg bg-[#2b2d31] border-[#3f4147] min-w-0">
          <p class="text-xs font-medium uppercase tracking-wide text-[#b5bac1]">Unicode Usage</p>
          <p class="mt-2 text-2xl font-semibold text-white break-words">{{ $stats['usage_by_type']['UNICODE'] ?? 0 }}</p>
        </div>
      </div>
    </div>

    <div class="grid gap-8">
      <div class="space-y-3">
        <h3 class="text-xl font-semibold text-white">Emote Usage Over Time (Last 30 Days)</h3>

        <div class="p-4 overflow-hidden border rounded-lg bg-[#2b2d31] border-[#3f4147] md:p-5">
          <div class="h-44 min-w-0 sm:h-56 md:h-64 lg:h-72">
            <canvas id="emoji-usage-over-time-chart" class="w-full h-full"></canvas>
          </div>
        </div>
      </div>

      <div class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-3">
          <h3 class="text-xl font-semibold text-white">Top Movers (Last 7 Days vs Previous 7 Days)</h3>

          <div class="overflow-hidden border rounded-lg bg-[#2b2d31] border-[#3f4147]">
            @forelse (($stats['top_movers'] ?? collect()) as $emote)
              <div class="flex flex-col items-start gap-2 px-4 py-3.5 border-b border-[#3f4147] sm:flex-row sm:items-center sm:justify-between sm:gap-3 last:border-b-0">
                <div class="flex items-center gap-3 min-w-0">
                  @if (($emote->type ?? null) === 'UNICODE')
                    <span class="font-medium truncate text-[#dbdee1]">{{ $emote->emote_name }}</span>
                  @else
                    <img src="{{ $emoteImageSrc($emote->image) }}" alt="{{ $emote->emote_name }}" class="object-cover w-9 h-9 border rounded border-[#3f4147]" />
                    <span class="font-medium truncate text-[#dbdee1]">{{ $emote->emote_name }}</span>
                  @endif
                </div>
                <div class="text-left sm:text-right sm:shrink-0">
                  <p class="text-sm text-[#b5bac1]">{{ $emote->previous_count }} → {{ $emote->current_count }}</p>
                  <p class="text-sm font-medium {{ $emote->delta >= 0 ? 'text-[#57f287]' : 'text-[#ed4245]' }}">
                    {{ $emote->delta >= 0 ? '+' : '' }}{{ $emote->delta }}
                  </p>
                </div>
              </div>
            @empty
              <p class="px-4 py-3 text-sm text-[#b5bac1]">No mover data available yet.</p>
            @endforelse
          </div>
        </div>

        <div class="space-y-3">
          <h3 class="text-xl font-semibold text-white">Top 10 Unicode Emotes</h3>

          <div class="overflow-hidden border rounded-lg bg-[#2b2d31] border-[#3f4147]">
            @forelse (($stats['top_unicode'] ?? collect()) as $emote)
              <div class="flex flex-col items-start gap-2 px-4 py-3.5 border-b border-[#3f4147] sm:flex-row sm:items-center sm:justify-between sm:gap-3 last:border-b-0">
                <div class="flex items-center gap-3 min-w-0">
                  <span class="w-8 text-sm font-semibold text-[#b5bac1]">#{{ $loop->iteration }}</span>
                  <span class="font-medium truncate text-[#dbdee1]">{{ $emote->emote_name }}</span>
                </div>
                <span class="text-sm font-medium text-[#b5bac1] sm:shrink-0">{{ $emote->total_usage }}</span>
              </div>
            @empty
              <p class="px-4 py-3 text-sm text-[#b5bac1]">No unicode emote usage found.</p>
            @endforelse
          </div>
        </div>
      </div>

      <div class="space-y-3">
        <h3 class="text-xl font-semibold text-white">Custom Emote Usage (Top & Bottom)</h3>

        <div class="grid gap-4 md:grid-cols-2">
          @foreach ($customUsageTables as $table)
            <div class="overflow-hidden border rounded-lg bg-[#2b2d31] border-[#3f4147]">
              <div class="px-4 py-3 border-b border-[#3f4147]">
                <h4 class="text-sm font-semibold text-white">{{ $table['title'] }}</h4>
              </div>

              @forelse ($table['list'] as $emote)
                <div class="flex flex-col items-start gap-2 px-4 py-3.5 border-b border-[#3f4147] sm:flex-row sm:items-center sm:justify-between sm:gap-3 last:border-b-0">
                  <div class="flex items-center gap-3 min-w-0">
                    <span class="w-8 text-sm font-semibold text-[#b5bac1]">#{{ $loop->iteration }}</span>
                    <img src="{{ $emoteImageSrc($emote->image) }}" alt="{{ $emote->emote_name }}" class="object-cover w-9 h-9 border rounded border-[#3f4147]" />
                    <span class="font-medium truncate text-[#dbdee1]">{{ $emote->emote_name }}</span>
                  </div>
                  <span class="text-sm font-medium text-[#b5bac1] sm:shrink-0">{{ $emote->total_usage }}</span>
                </div>
              @empty
                <p class="px-4 py-3 text-sm text-[#b5bac1]">{{ $table['empty'] }}</p>
              @endforelse
            </div>
          @endforeach
        </div>
      </div>
    </div>

  <script id="homepage-chart-json" type="application/json">@json($chartPayload)</script>

  @once
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      window.hermesCharts = window.hermesCharts || {};

      function hermesDestroyChart(key) {
        if (window.hermesCharts[key]) {
          window.hermesCharts[key].destroy();
          delete window.hermesCharts[key];
        }
      }

      function hermesGetChartPayload() {
        const statsElement = document.getElementById('homepage-chart-json');

        if (!statsElement) {
          return null;
        }

        try {
          return JSON.parse(statsElement.textContent || '{}');
        } catch (error) {
          return null;
        }
      }

      function hermesRenderOverTimeChart(payloadOverride) {
        if (typeof Chart === 'undefined') {
          return;
        }

        const payload = payloadOverride || hermesGetChartPayload();

        if (!payload) {
          return;
        }

        const usageOverTimeCanvas = document.getElementById('emoji-usage-over-time-chart');
        const usageOverTime = payload.usage_over_time || { labels: [], values: [] };
        const isMobileViewport = window.matchMedia('(max-width: 639px)').matches;

        if (usageOverTimeCanvas) {
          hermesDestroyChart('usageOverTime');
          window.hermesCharts.usageOverTime = new Chart(usageOverTimeCanvas, {
            type: 'line',
            data: {
              labels: usageOverTime.labels || [],
              datasets: [{
                label: 'Usage',
                data: usageOverTime.values || [],
                tension: 0.3,
                fill: false,
                borderColor: '#5865f2',
                borderWidth: 2,
                pointBackgroundColor: '#5865f2',
                pointBorderColor: '#5865f2',
                pointRadius: isMobileViewport ? 0 : 2,
                pointHoverRadius: isMobileViewport ? 2 : 4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              interaction: {
                mode: 'index',
                intersect: false
              },
              plugins: {
                legend: {
                  display: !isMobileViewport,
                  labels: {
                    color: '#dbdee1'
                  }
                }
              },
              scales: {
                x: {
                  ticks: {
                    color: '#b5bac1',
                    autoSkip: true,
                    maxTicksLimit: isMobileViewport ? 4 : 8,
                    maxRotation: 0,
                    minRotation: 0
                  },
                  grid: {
                    color: '#3f4147',
                    display: !isMobileViewport
                  }
                },
                y: {
                  beginAtZero: true,
                  ticks: {
                    precision: 0,
                    color: '#b5bac1',
                    maxTicksLimit: isMobileViewport ? 5 : 8
                  },
                  grid: {
                    color: '#3f4147'
                  }
                }
              }
            }
          });
        }
      }

      document.addEventListener('DOMContentLoaded', function () {
        hermesRenderOverTimeChart();

        window.addEventListener('resize', function () {
          hermesRenderOverTimeChart();
        });
      });
    </script>
  @endonce
</div>
</x-layouts.app>
