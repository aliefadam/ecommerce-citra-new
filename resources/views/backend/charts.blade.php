@extends('layouts.app')

@section('title', 'Charts')

@section('content')
<main class="flex-1 p-4 sm:p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Charts</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Visual analytics powered by Chart.js — 6 chart types.</p>
    </div>

    <!-- Row 1: Line + Bar -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-4">
      <!-- Line Chart -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex items-center justify-between mb-1">
          <div>
            <h2 class="font-bold text-slate-800 dark:text-white">Monthly Visitors</h2>
            <p class="text-xs text-slate-400 mt-0.5">Unique site visitors per month</p>
          </div>
          <span class="text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-3 py-1 rounded-full">Line</span>
        </div>
        <div class="flex items-center gap-4 mt-3 mb-4">
          <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"><span class="w-4 h-1 bg-blue-500 rounded-full inline-block"></span>2024</div>
          <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"><span class="w-4 h-1 bg-violet-400 rounded-full inline-block"></span>2023</div>
        </div>
        <canvas id="lineChart1" height="140"></canvas>
      </div>

      <!-- Bar Chart -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex items-center justify-between mb-1">
          <div>
            <h2 class="font-bold text-slate-800 dark:text-white">Sales by Category</h2>
            <p class="text-xs text-slate-400 mt-0.5">Product category breakdown</p>
          </div>
          <span class="text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-full">Bar</span>
        </div>
        <canvas id="barChart" height="150" class="mt-4"></canvas>
      </div>
    </div>

    <!-- Row 2: Doughnut + Polar + Radar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-4">
      <!-- Doughnut -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-bold text-slate-800 dark:text-white">Revenue Split</h2>
            <p class="text-xs text-slate-400 mt-0.5">By product line</p>
          </div>
          <span class="text-xs font-semibold bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 px-3 py-1 rounded-full">Doughnut</span>
        </div>
        <canvas id="doughnutChart" height="200"></canvas>
        <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
          <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-blue-500 flex-shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Software 38%</span></div>
          <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-violet-500 flex-shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Hardware 27%</span></div>
          <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-emerald-500 flex-shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Services 22%</span></div>
          <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-amber-400 flex-shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Other 13%</span></div>
        </div>
      </div>

      <!-- Polar Area -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-bold text-slate-800 dark:text-white">Channel Performance</h2>
            <p class="text-xs text-slate-400 mt-0.5">Marketing channels</p>
          </div>
          <span class="text-xs font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-3 py-1 rounded-full">Polar</span>
        </div>
        <canvas id="polarChart" height="220"></canvas>
      </div>

      <!-- Radar -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-bold text-slate-800 dark:text-white">Skills Assessment</h2>
            <p class="text-xs text-slate-400 mt-0.5">Team competency scores</p>
          </div>
          <span class="text-xs font-semibold bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 px-3 py-1 rounded-full">Radar</span>
        </div>
        <canvas id="radarChart" height="220"></canvas>
      </div>
    </div>

    <!-- Row 3: Stacked Bar Full Width -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
      <div class="flex items-center justify-between mb-1">
        <div>
          <h2 class="font-bold text-slate-800 dark:text-white">Quarterly Revenue Breakdown</h2>
          <p class="text-xs text-slate-400 mt-0.5">Stacked by product line — 2024</p>
        </div>
        <span class="text-xs font-semibold bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 px-3 py-1 rounded-full">Stacked Bar</span>
      </div>
      <div class="flex flex-wrap items-center gap-4 mt-3 mb-4">
        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"><span class="w-3 h-3 rounded-sm bg-blue-500 inline-block"></span>Software</div>
        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"><span class="w-3 h-3 rounded-sm bg-violet-500 inline-block"></span>Hardware</div>
        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"><span class="w-3 h-3 rounded-sm bg-emerald-500 inline-block"></span>Services</div>
      </div>
      <canvas id="stackedChart" height="100"></canvas>
    </div>
  </main>
@endsection

@section('script')
<script>
  const gc = () => document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
  const tc = () => document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b';
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const quarters = ['Q1 2024','Q2 2024','Q3 2024','Q4 2024'];

  let charts = {};

  function buildCharts() {
    // Line
    const lCtx = document.getElementById('lineChart1').getContext('2d');
    const g1 = lCtx.createLinearGradient(0,0,0,300);
    g1.addColorStop(0,'rgba(59,130,246,0.2)'); g1.addColorStop(1,'rgba(59,130,246,0)');
    const g2 = lCtx.createLinearGradient(0,0,0,300);
    g2.addColorStop(0,'rgba(139,92,246,0.15)'); g2.addColorStop(1,'rgba(139,92,246,0)');
    charts.line = new Chart(lCtx, {
      type:'line',
      data:{
        labels:months,
        datasets:[
          { label:'2024', data:[8200,9500,8800,12000,14200,16500,15000,18300,17100,21000,19500,24000], borderColor:'#3b82f6', backgroundColor:g1, borderWidth:2.5, tension:0.4, fill:true, pointRadius:4, pointHoverRadius:6, pointBackgroundColor:'#3b82f6' },
          { label:'2023', data:[6000,7200,6800,9000,10500,12000,11200,13500,12800,15000,14200,17000], borderColor:'#a78bfa', backgroundColor:g2, borderWidth:2, tension:0.4, fill:true, pointRadius:3, pointHoverRadius:5, pointBackgroundColor:'#a78bfa' }
        ]
      },
      options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{color:gc()},ticks:{color:tc(),font:{family:'Plus Jakarta Sans',size:11}}}, y:{grid:{color:gc()},ticks:{color:tc(),font:{family:'Plus Jakarta Sans',size:11}}} }}
    });

    // Bar
    charts.bar = new Chart(document.getElementById('barChart'), {
      type:'bar',
      data:{
        labels:['Electronics','Clothing','Books','Software','Hardware','Services'],
        datasets:[{ data:[4200,3100,1800,5600,2900,3800], backgroundColor:['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ec4899','#06b6d4'], borderRadius:8, borderSkipped:false }]
      },
      options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{display:false},ticks:{color:tc(),font:{family:'Plus Jakarta Sans',size:11}}}, y:{grid:{color:gc()},ticks:{color:tc(),font:{family:'Plus Jakarta Sans',size:11}}} }}
    });

    // Doughnut
    charts.doughnut = new Chart(document.getElementById('doughnutChart'), {
      type:'doughnut',
      data:{
        labels:['Software','Hardware','Services','Other'],
        datasets:[{ data:[38,27,22,13], backgroundColor:['#3b82f6','#8b5cf6','#10b981','#f59e0b'], borderWidth:0, hoverOffset:8 }]
      },
      options:{ responsive:true, cutout:'72%', plugins:{legend:{display:false}} }
    });

    // Polar
    charts.polar = new Chart(document.getElementById('polarChart'), {
      type:'polarArea',
      data:{
        labels:['Organic','Paid','Email','Social','Referral'],
        datasets:[{ data:[42,28,18,35,22], backgroundColor:['rgba(59,130,246,0.7)','rgba(139,92,246,0.7)','rgba(16,185,129,0.7)','rgba(245,158,11,0.7)','rgba(236,72,153,0.7)'], borderWidth:0 }]
      },
      options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{font:{family:'Plus Jakarta Sans',size:11},color:tc(),boxWidth:10,padding:12}}}, scales:{r:{ticks:{display:false},grid:{color:gc()}}} }
    });

    // Radar
    charts.radar = new Chart(document.getElementById('radarChart'), {
      type:'radar',
      data:{
        labels:['Design','Dev','Marketing','Sales','Support','Analytics'],
        datasets:[
          { label:'Team A', data:[85,92,78,65,88,74], borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.15)', borderWidth:2, pointBackgroundColor:'#3b82f6', pointRadius:4 },
          { label:'Team B', data:[70,78,88,80,72,90], borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.15)', borderWidth:2, pointBackgroundColor:'#10b981', pointRadius:4 }
        ]
      },
      options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{font:{family:'Plus Jakarta Sans',size:11},color:tc(),boxWidth:10,padding:12}}}, scales:{r:{ticks:{font:{family:'Plus Jakarta Sans',size:10},color:tc()},grid:{color:gc()},pointLabels:{font:{family:'Plus Jakarta Sans',size:11},color:tc()}}} }
    });

    // Stacked
    charts.stacked = new Chart(document.getElementById('stackedChart'), {
      type:'bar',
      data:{
        labels:quarters,
        datasets:[
          { label:'Software', data:[28000,34000,31000,42000], backgroundColor:'#3b82f6', borderRadius:{topLeft:0,topRight:0}, stack:'s' },
          { label:'Hardware', data:[18000,22000,20000,27000], backgroundColor:'#8b5cf6', stack:'s' },
          { label:'Services', data:[12000,15000,13000,18000], backgroundColor:'#10b981', borderRadius:{topLeft:8,topRight:8}, stack:'s' }
        ]
      },
      options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{display:false},ticks:{color:tc(),font:{family:'Plus Jakarta Sans',size:12}}}, y:{grid:{color:gc()},ticks:{color:tc(),font:{family:'Plus Jakarta Sans',size:11},callback:v=>'$'+(v/1000).toFixed(0)+'k'}, stacked:true } }}
    });
  }

  function rebuildCharts() {
    Object.values(charts).forEach(c=>c.destroy());
    charts = {};
    buildCharts();
  }

  window.addEventListener('theme:changed', rebuildCharts);
  buildCharts();
</script>
@endsection
