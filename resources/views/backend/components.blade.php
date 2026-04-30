@extends('layouts.app')

@section('title', 'Components')

@section('content')
<main class="flex-1 p-4 sm:p-6 space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Components</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ready-to-use UI components — copy and use instantly.</p>
    </div>

    <!-- BUTTONS -->
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
      <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Buttons</h2>
      <p class="text-xs text-slate-400 mb-5">Various button styles and sizes.</p>
      <div class="space-y-4">
        <div class="flex flex-wrap gap-3">
          <button class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-blue-200 dark:shadow-blue-900/30">Primary</button>
          <button class="px-5 py-2.5 bg-slate-800 dark:bg-slate-200 hover:bg-slate-700 dark:hover:bg-slate-300 text-white dark:text-slate-800 text-sm font-semibold rounded-xl transition-colors">Secondary</button>
          <button class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-emerald-200 dark:shadow-emerald-900/30">Success</button>
          <button class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition-colors">Warning</button>
          <button class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-xl transition-colors">Danger</button>
          <button class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl transition-colors">Purple</button>
        </div>
        <div class="flex flex-wrap gap-3">
          <button class="px-5 py-2.5 border-2 border-blue-600 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-sm font-semibold rounded-xl transition-colors">Outline Primary</button>
          <button class="px-5 py-2.5 border-2 border-emerald-500 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-sm font-semibold rounded-xl transition-colors">Outline Success</button>
          <button class="px-5 py-2.5 border-2 border-red-500 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-semibold rounded-xl transition-colors">Outline Danger</button>
          <button class="px-5 py-2.5 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-semibold rounded-xl transition-colors">Ghost</button>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <button class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add New
          </button>
          <button class="flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Approve
          </button>
          <button class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">Small</button>
          <button class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold rounded-xl transition-colors">Large</button>
          <button disabled class="px-5 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 text-sm font-semibold rounded-xl cursor-not-allowed">Disabled</button>
          <!-- Loading button -->
          <button class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl opacity-80 cursor-not-allowed">
            <svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            Loading...
          </button>
        </div>
      </div>
    </section>

    <!-- BADGES -->
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
      <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Badges & Pills</h2>
      <p class="text-xs text-slate-400 mb-5">Status indicators and labels.</p>
      <div class="flex flex-wrap gap-3">
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">Primary</span>
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">Success</span>
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">Warning</span>
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">Danger</span>
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400">Purple</span>
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">Neutral</span>
        <span class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>Active</span>
        <span class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400"><span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>Offline</span>
        <span class="px-3 py-1 rounded-lg text-xs font-bold bg-blue-600 text-white">New</span>
        <span class="px-3 py-1 rounded-lg text-xs font-bold bg-red-500 text-white">Hot</span>
      </div>
    </section>

    <!-- ALERTS -->
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
      <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Alerts</h2>
      <p class="text-xs text-slate-400 mb-5">Notification and alert banners.</p>
      <div class="space-y-3">
        <div class="flex items-start gap-3 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
          <svg class="mt-0.5 flex-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <div class="flex-1">
            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Information</p>
            <p class="text-xs text-blue-700 dark:text-blue-400 mt-0.5">This is an informational alert — check it out!</p>
          </div>
          <button class="text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 transition-colors" onclick="this.closest('div.flex').remove()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
          <svg class="mt-0.5 flex-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <div class="flex-1">
            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Success</p>
            <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">Your changes have been saved successfully.</p>
          </div>
          <button class="text-emerald-400 hover:text-emerald-600 transition-colors" onclick="this.closest('div.flex').remove()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
          <svg class="mt-0.5 flex-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <div class="flex-1">
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Warning</p>
            <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">Please review your account settings before continuing.</p>
          </div>
          <button class="text-amber-400 hover:text-amber-600 transition-colors" onclick="this.closest('div.flex').remove()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="flex items-start gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
          <svg class="mt-0.5 flex-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          <div class="flex-1">
            <p class="text-sm font-semibold text-red-800 dark:text-red-300">Error</p>
            <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">Something went wrong. Please try again later.</p>
          </div>
          <button class="text-red-400 hover:text-red-600 transition-colors" onclick="this.closest('div.flex').remove()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>
    </section>

    <!-- FORM ELEMENTS -->
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
      <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Form Elements</h2>
      <p class="text-xs text-slate-400 mb-5">Input fields, selects, checkboxes, toggles.</p>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Text Input</label>
          <input type="text" placeholder="Enter your name..." class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400 transition-all"/>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email Input</label>
          <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input type="email" placeholder="you@example.com" class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400"/>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
          <div class="relative">
            <input id="pwInput" type="password" placeholder="••••••••" class="w-full px-4 pr-11 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400"/>
            <button onclick="togglePw()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Select</label>
          <select class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
            <option>Choose an option...</option>
            <option>Option 1</option>
            <option>Option 2</option>
            <option>Option 3</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Textarea</label>
          <textarea rows="3" placeholder="Write something..." class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400 resize-none"></textarea>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Error State</label>
          <input type="text" value="invalid@" class="w-full px-4 py-2.5 text-sm border-2 border-red-400 rounded-xl bg-red-50 dark:bg-red-900/10 focus:outline-none focus:ring-2 focus:ring-red-400 dark:text-slate-200 dark:border-red-600"/>
          <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Please enter a valid email address.</p>
        </div>
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Checkboxes</label>
          <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" checked class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 w-4 h-4"/><span class="text-sm text-slate-600 dark:text-slate-300">Option A (checked)</span></label>
          <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 w-4 h-4"/><span class="text-sm text-slate-600 dark:text-slate-300">Option B</span></label>
          <label class="flex items-center gap-3 cursor-not-allowed opacity-50"><input type="checkbox" disabled class="rounded w-4 h-4"/><span class="text-sm text-slate-600 dark:text-slate-300">Option C (disabled)</span></label>
        </div>
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Toggle Switches</label>
          <div class="flex items-center gap-3">
            <label class="toggle-switch"><input type="checkbox" checked/><span class="slider"></span></label>
            <span class="text-sm text-slate-600 dark:text-slate-300">Notifications enabled</span>
          </div>
          <div class="flex items-center gap-3">
            <label class="toggle-switch"><input type="checkbox"/><span class="slider"></span></label>
            <span class="text-sm text-slate-600 dark:text-slate-300">Auto-updates</span>
          </div>
          <div class="flex items-center gap-3">
            <label class="toggle-switch opacity-50"><input type="checkbox" disabled/><span class="slider cursor-not-allowed"></span></label>
            <span class="text-sm text-slate-600 dark:text-slate-300 opacity-50">Disabled toggle</span>
          </div>
        </div>
      </div>
    </section>

    <!-- CARDS -->
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
      <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Cards</h2>
      <p class="text-xs text-slate-400 mb-5">Different card styles for content display.</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Basic Card -->
        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-5 border border-slate-200 dark:border-slate-600 hover:shadow-md transition-shadow">
          <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mb-3">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
          </div>
          <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Analytics</h3>
          <p class="text-xs text-slate-400 leading-relaxed">Track performance metrics across all channels in real-time.</p>
          <button class="mt-3 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">View Report →</button>
        </div>
        <!-- Profile Card -->
        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-5 border border-slate-200 dark:border-slate-600 text-center hover:shadow-md transition-shadow">
          <img src="https://ui-avatars.com/api/?name=Alice+Brown&background=8b5cf6&color=fff&size=80" class="w-16 h-16 rounded-full mx-auto ring-4 ring-violet-200 dark:ring-violet-800 mb-3"/>
          <h3 class="font-bold text-slate-800 dark:text-white text-sm">Alice Brown</h3>
          <p class="text-xs text-slate-400 mt-0.5">Product Designer</p>
          <div class="flex justify-center gap-4 mt-3 text-xs">
            <div><p class="font-bold text-slate-800 dark:text-white">128</p><p class="text-slate-400">Posts</p></div>
            <div><p class="font-bold text-slate-800 dark:text-white">4.2k</p><p class="text-slate-400">Followers</p></div>
            <div><p class="font-bold text-slate-800 dark:text-white">312</p><p class="text-slate-400">Following</p></div>
          </div>
          <button class="mt-4 w-full px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold rounded-lg transition-colors">Follow</button>
        </div>
        <!-- Stat Card -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-5 text-white hover:shadow-lg hover:shadow-blue-300/50 dark:hover:shadow-blue-900/50 transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-semibold text-blue-100">Total Sales</span>
            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
          </div>
          <p class="text-3xl font-bold">$84,254</p>
          <p class="text-blue-200 text-xs mt-1">+15.3% from last month</p>
          <div class="mt-4 h-1.5 bg-white/20 rounded-full overflow-hidden">
            <div class="h-full bg-white rounded-full w-4/5"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- PROGRESS + SPINNERS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Progress Bars</h2>
        <p class="text-xs text-slate-400 mb-5">Indicate completion or loading state.</p>
        <div class="space-y-4">
          <div>
            <div class="flex justify-between text-xs mb-1.5"><span class="font-medium text-slate-600 dark:text-slate-300">Storage Used</span><span class="text-blue-600">78%</span></div>
            <div class="h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"><div class="h-full bg-blue-500 rounded-full" style="width:78%"></div></div>
          </div>
          <div>
            <div class="flex justify-between text-xs mb-1.5"><span class="font-medium text-slate-600 dark:text-slate-300">Monthly Goal</span><span class="text-emerald-600">92%</span></div>
            <div class="h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:92%"></div></div>
          </div>
          <div>
            <div class="flex justify-between text-xs mb-1.5"><span class="font-medium text-slate-600 dark:text-slate-300">Tasks Complete</span><span class="text-amber-600">45%</span></div>
            <div class="h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"><div class="h-full bg-amber-500 rounded-full" style="width:45%"></div></div>
          </div>
          <div>
            <div class="flex justify-between text-xs mb-1.5"><span class="font-medium text-slate-600 dark:text-slate-300">Budget Spent</span><span class="text-red-600">65%</span></div>
            <div class="h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden"><div class="h-full bg-red-500 rounded-full" style="width:65%"></div></div>
          </div>
          <div>
            <div class="flex justify-between text-xs mb-1.5"><span class="font-medium text-slate-600 dark:text-slate-300">Striped Animated</span><span class="text-violet-600">55%</span></div>
            <div class="h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
              <div class="h-full rounded-full" style="width:55%;background: repeating-linear-gradient(45deg,#8b5cf6,#8b5cf6 10px,#7c3aed 10px,#7c3aed 20px);animation:slide 1s linear infinite;background-size:28px 100%;"></div>
            </div>
          </div>
        </div>
        <style>@keyframes slide{from{background-position:0 0}to{background-position:28px 0}}</style>
      </section>

      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Spinners & Skeletons</h2>
        <p class="text-xs text-slate-400 mb-5">Loading state indicators.</p>
        <div class="flex flex-wrap items-center gap-5 mb-6">
          <div class="flex flex-col items-center gap-2">
            <div class="w-10 h-10 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"></div>
            <span class="text-xs text-slate-400">Default</span>
          </div>
          <div class="flex flex-col items-center gap-2">
            <div class="w-8 h-8 rounded-full border-4 border-emerald-200 border-t-emerald-600 animate-spin"></div>
            <span class="text-xs text-slate-400">Success</span>
          </div>
          <div class="flex flex-col items-center gap-2">
            <div class="w-8 h-8 rounded-full border-4 border-red-200 border-t-red-600 animate-spin"></div>
            <span class="text-xs text-slate-400">Danger</span>
          </div>
          <div class="flex flex-col items-center gap-2">
            <div class="w-6 h-6 rounded-full border-3 border-violet-200 border-t-violet-600 animate-spin" style="border-width:3px"></div>
            <span class="text-xs text-slate-400">Small</span>
          </div>
          <div class="flex flex-col items-center gap-2">
            <div class="flex gap-1">
              <div class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-bounce" style="animation-delay:0s"></div>
              <div class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-bounce" style="animation-delay:0.15s"></div>
              <div class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-bounce" style="animation-delay:0.3s"></div>
            </div>
            <span class="text-xs text-slate-400">Dots</span>
          </div>
        </div>
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-3">Skeleton Loaders</p>
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 animate-pulse flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full animate-pulse w-3/4"></div>
              <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full animate-pulse w-1/2"></div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 animate-pulse flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full animate-pulse w-2/3"></div>
              <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full animate-pulse w-5/6"></div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- AVATARS & TOOLTIPS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Avatars</h2>
        <p class="text-xs text-slate-400 mb-5">User avatar sizes, groups, and status indicators.</p>
        <div class="flex flex-wrap items-end gap-4 mb-5">
          <div class="flex flex-col items-center gap-2"><img src="https://ui-avatars.com/api/?name=A+B&background=3b82f6&color=fff&size=96" class="w-16 h-16 rounded-full ring-4 ring-blue-100 dark:ring-blue-900/40"/><span class="text-xs text-slate-400">XL</span></div>
          <div class="flex flex-col items-center gap-2"><img src="https://ui-avatars.com/api/?name=C+D&background=8b5cf6&color=fff&size=64" class="w-12 h-12 rounded-full"/><span class="text-xs text-slate-400">LG</span></div>
          <div class="flex flex-col items-center gap-2"><img src="https://ui-avatars.com/api/?name=E+F&background=10b981&color=fff&size=64" class="w-10 h-10 rounded-full"/><span class="text-xs text-slate-400">MD</span></div>
          <div class="flex flex-col items-center gap-2"><img src="https://ui-avatars.com/api/?name=G+H&background=f59e0b&color=fff&size=32" class="w-8 h-8 rounded-full"/><span class="text-xs text-slate-400">SM</span></div>
          <div class="flex flex-col items-center gap-2"><img src="https://ui-avatars.com/api/?name=I+J&background=ef4444&color=fff&size=24" class="w-6 h-6 rounded-full"/><span class="text-xs text-slate-400">XS</span></div>
        </div>
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-3">With Status</p>
        <div class="flex gap-4 mb-5">
          <div class="relative"><img src="https://ui-avatars.com/api/?name=Online&background=3b82f6&color=fff&size=64" class="w-12 h-12 rounded-full"/><span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 rounded-full ring-2 ring-white dark:ring-slate-800"></span></div>
          <div class="relative"><img src="https://ui-avatars.com/api/?name=Away&background=8b5cf6&color=fff&size=64" class="w-12 h-12 rounded-full"/><span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-amber-400 rounded-full ring-2 ring-white dark:ring-slate-800"></span></div>
          <div class="relative"><img src="https://ui-avatars.com/api/?name=Busy&background=ec4899&color=fff&size=64" class="w-12 h-12 rounded-full"/><span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-red-500 rounded-full ring-2 ring-white dark:ring-slate-800"></span></div>
          <div class="relative"><img src="https://ui-avatars.com/api/?name=Off&background=94a3b8&color=fff&size=64" class="w-12 h-12 rounded-full"/><span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-slate-400 rounded-full ring-2 ring-white dark:ring-slate-800"></span></div>
        </div>
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-3">Avatar Group</p>
        <div class="flex -space-x-3">
          <img src="https://ui-avatars.com/api/?name=A&background=3b82f6&color=fff&size=64" class="w-10 h-10 rounded-full ring-2 ring-white dark:ring-slate-800"/>
          <img src="https://ui-avatars.com/api/?name=B&background=8b5cf6&color=fff&size=64" class="w-10 h-10 rounded-full ring-2 ring-white dark:ring-slate-800"/>
          <img src="https://ui-avatars.com/api/?name=C&background=10b981&color=fff&size=64" class="w-10 h-10 rounded-full ring-2 ring-white dark:ring-slate-800"/>
          <img src="https://ui-avatars.com/api/?name=D&background=f59e0b&color=fff&size=64" class="w-10 h-10 rounded-full ring-2 ring-white dark:ring-slate-800"/>
          <div class="w-10 h-10 rounded-full ring-2 ring-white dark:ring-slate-800 bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-xs font-semibold text-slate-600 dark:text-slate-300">+8</div>
        </div>
      </section>

      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Tabs</h2>
        <p class="text-xs text-slate-400 mb-5">Navigation tab components.</p>
        <!-- Tab 1 -->
        <div class="flex gap-1 bg-slate-100 dark:bg-slate-700/60 p-1 rounded-xl mb-4">
          <button onclick="switchTab('pill','overview')" id="pill-overview" class="flex-1 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-slate-600 text-slate-800 dark:text-white shadow-sm transition-all">Overview</button>
          <button onclick="switchTab('pill','analytics')" id="pill-analytics" class="flex-1 py-2 text-xs font-semibold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-all">Analytics</button>
          <button onclick="switchTab('pill','reports')" id="pill-reports" class="flex-1 py-2 text-xs font-semibold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-all">Reports</button>
        </div>
        <div id="tab-content" class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-4 text-sm text-slate-600 dark:text-slate-300 mb-5">
          📊 <strong>Overview</strong> — Summary of your most important metrics and KPIs at a glance.
        </div>
        <!-- Underline Tabs -->
        <div class="border-b border-slate-200 dark:border-slate-700 flex gap-6 mb-4">
          <button onclick="switchTab('line','tab1')" id="line-tab1" class="pb-2.5 text-sm font-semibold text-blue-600 border-b-2 border-blue-600 -mb-px transition-all">Dashboard</button>
          <button onclick="switchTab('line','tab2')" id="line-tab2" class="pb-2.5 text-sm font-medium text-slate-500 dark:text-slate-400 border-b-2 border-transparent hover:text-slate-700 dark:hover:text-slate-200 transition-all">Users</button>
          <button onclick="switchTab('line','tab3')" id="line-tab3" class="pb-2.5 text-sm font-medium text-slate-500 dark:text-slate-400 border-b-2 border-transparent hover:text-slate-700 dark:hover:text-slate-200 transition-all">Orders</button>
        </div>
        <p id="line-content" class="text-sm text-slate-500 dark:text-slate-400">Showing <strong class="text-slate-700 dark:text-slate-200">Dashboard</strong> content area.</p>
      </section>
    </div>

    <!-- ACCORDION -->
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
      <h2 class="font-bold text-slate-800 dark:text-white text-base mb-1">Accordion / FAQ</h2>
      <p class="text-xs text-slate-400 mb-5">Collapsible content sections.</p>
      <div class="space-y-2" id="accordion">
        <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
          <button onclick="toggleAccordion(0)" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
            <span class="text-sm font-semibold text-slate-800 dark:text-white">What is AdminKit?</span>
            <svg id="acc-icon-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-300 rotate-180"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div id="acc-body-0" class="px-5 pb-4 text-sm text-slate-500 dark:text-slate-400">
            AdminKit is a modern, responsive admin panel template built with Tailwind CSS and Plus Jakarta Sans font, featuring dark mode, charts, datatables, and rich UI components.
          </div>
        </div>
        <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
          <button onclick="toggleAccordion(1)" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
            <span class="text-sm font-semibold text-slate-800 dark:text-white">How do I enable dark mode?</span>
            <svg id="acc-icon-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-300"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div id="acc-body-1" class="hidden px-5 pb-4 text-sm text-slate-500 dark:text-slate-400">
            Click the moon/sun icon in the top navigation bar to toggle dark mode. Your preference is saved in localStorage and persists across page reloads.
          </div>
        </div>
        <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
          <button onclick="toggleAccordion(2)" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
            <span class="text-sm font-semibold text-slate-800 dark:text-white">Can I customize the components?</span>
            <svg id="acc-icon-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-300"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div id="acc-body-2" class="hidden px-5 pb-4 text-sm text-slate-500 dark:text-slate-400">
            Absolutely! All components use Tailwind CSS utility classes and are fully customizable. Simply modify the class names or extend the Tailwind config to match your brand.
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection

@section('script')
<script>
  function togglePw() {
    const i = document.getElementById('pwInput');
    i.type = i.type === 'password' ? 'text' : 'password';
  }

  // Tabs
  const pillContents = {
    overview: '📊 <strong>Overview</strong> — Summary of your most important metrics and KPIs at a glance.',
    analytics: '📈 <strong>Analytics</strong> — Detailed breakdowns of traffic, conversions, and engagement rates.',
    reports: '📋 <strong>Reports</strong> — Download and schedule automated reports for your team.',
  };
  const lineContents = { tab1:'Dashboard', tab2:'Users', tab3:'Orders' };

  function switchTab(type, key) {
    if(type === 'pill') {
      ['overview','analytics','reports'].forEach(t => {
        const btn = document.getElementById(`pill-${t}`);
        btn.className = t === key
          ? 'flex-1 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-slate-600 text-slate-800 dark:text-white shadow-sm transition-all'
          : 'flex-1 py-2 text-xs font-semibold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-all';
      });
      document.getElementById('tab-content').innerHTML = pillContents[key];
    } else {
      ['tab1','tab2','tab3'].forEach(t => {
        const btn = document.getElementById(`line-${t}`);
        btn.className = t === key
          ? 'pb-2.5 text-sm font-semibold text-blue-600 border-b-2 border-blue-600 -mb-px transition-all'
          : 'pb-2.5 text-sm font-medium text-slate-500 dark:text-slate-400 border-b-2 border-transparent hover:text-slate-700 dark:hover:text-slate-200 transition-all';
      });
      document.getElementById('line-content').innerHTML = `Showing <strong class="text-slate-700 dark:text-slate-200">${lineContents[key]}</strong> content area.`;
    }
  }

  // Accordion
  function toggleAccordion(i) {
    const body = document.getElementById(`acc-body-${i}`);
    const icon = document.getElementById(`acc-icon-${i}`);
    const open = !body.classList.contains('hidden');
    // Close all
    [0,1,2].forEach(j => {
      document.getElementById(`acc-body-${j}`).classList.add('hidden');
      document.getElementById(`acc-icon-${j}`).classList.remove('rotate-180');
    });
    if(!open) { body.classList.remove('hidden'); icon.classList.add('rotate-180'); }
  }
</script>
@endsection
