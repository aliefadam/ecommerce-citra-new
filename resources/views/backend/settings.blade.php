@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<main class="flex-1 p-4 sm:p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Settings</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your account preferences and system configuration.</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
      <!-- Settings Navigation -->
      <div class="lg:w-56 flex-shrink-0">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-2">
          <nav class="space-y-0.5">
            <button onclick="showTab('profile')" id="nav-profile" class="settings-tab active w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Profile
            </button>
            <button onclick="showTab('account')" id="nav-account" class="settings-tab w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Security
            </button>
            <button onclick="showTab('notifications')" id="nav-notifications" class="settings-tab w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              Notifications
            </button>
            <button onclick="showTab('appearance')" id="nav-appearance" class="settings-tab w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
              Appearance
            </button>
            <button onclick="showTab('integrations')" id="nav-integrations" class="settings-tab w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white transition-all text-left">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
              Integrations
            </button>
            <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>
            <button onclick="showTab('danger')" id="nav-danger" class="settings-tab w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-red-500 hover:text-red-600 transition-all text-left">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              Danger Zone
            </button>
          </nav>
        </div>
      </div>

      <!-- Settings Content -->
      <div class="flex-1 space-y-5">
        <!-- PROFILE TAB -->
        <div id="tab-profile" class="settings-content">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-5">
            <h2 class="font-bold text-slate-800 dark:text-white mb-1">Profile Information</h2>
            <p class="text-xs text-slate-400 mb-6">Update your personal details and public profile.</p>
            <!-- Avatar -->
            <div class="flex items-center gap-5 mb-6 pb-6 border-b border-slate-100 dark:border-slate-700">
              <div class="relative">
                <img id="profileAvatar" src="https://ui-avatars.com/api/?name=Admin+User&background=3b82f6&color=fff&size=128" class="w-20 h-20 rounded-2xl ring-4 ring-blue-100 dark:ring-blue-900/40"/>
                <label class="absolute -bottom-1.5 -right-1.5 w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center cursor-pointer shadow-lg hover:bg-blue-700 transition-colors">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                  <input type="file" class="hidden" accept="image/*"/>
                </label>
              </div>
              <div>
                <p class="font-semibold text-slate-800 dark:text-white">Admin User</p>
                <p class="text-xs text-slate-400 mt-0.5">admin@adminkit.io</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1.5 cursor-pointer hover:underline">Change photo</p>
              </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">First Name</label>
                <input type="text" value="Admin" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200"/>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Last Name</label>
                <input type="text" value="User" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200"/>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Email Address</label>
                <input type="email" value="admin@adminkit.io" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200"/>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Phone</label>
                <input type="tel" value="+1 (555) 000-0000" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200"/>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Role</label>
                <select class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                  <option selected>Administrator</option>
                  <option>Editor</option>
                  <option>Viewer</option>
                </select>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Bio</label>
                <textarea rows="3" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 resize-none" placeholder="Tell us about yourself...">Administrator of AdminKit platform.</textarea>
              </div>
            </div>
            <div class="flex justify-end mt-5 gap-3">
              <button class="px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
              <button onclick="showSaved()" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Save Changes</button>
            </div>
          </div>
        </div>

        <!-- SECURITY TAB -->
        <div id="tab-account" class="settings-content hidden">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-5">
            <h2 class="font-bold text-slate-800 dark:text-white mb-1">Change Password</h2>
            <p class="text-xs text-slate-400 mb-6">Use a strong password of at least 8 characters.</p>
            <div class="space-y-4 max-w-md">
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Current Password</label>
                <input type="password" placeholder="••••••••" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400"/>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">New Password</label>
                <input type="password" id="newpw" placeholder="••••••••" oninput="checkStrength()" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400"/>
                <div class="mt-2 flex gap-1">
                  <div id="s1" class="h-1.5 flex-1 rounded-full bg-slate-200 dark:bg-slate-700 transition-colors"></div>
                  <div id="s2" class="h-1.5 flex-1 rounded-full bg-slate-200 dark:bg-slate-700 transition-colors"></div>
                  <div id="s3" class="h-1.5 flex-1 rounded-full bg-slate-200 dark:bg-slate-700 transition-colors"></div>
                  <div id="s4" class="h-1.5 flex-1 rounded-full bg-slate-200 dark:bg-slate-700 transition-colors"></div>
                </div>
                <p id="strengthLabel" class="text-xs text-slate-400 mt-1">Password strength</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Confirm New Password</label>
                <input type="password" placeholder="••••••••" class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200 placeholder-slate-400"/>
              </div>
            </div>
            <div class="flex gap-3 mt-5">
              <button onclick="showSaved()" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Update Password</button>
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="font-bold text-slate-800 dark:text-white mb-1">Two-Factor Authentication</h2>
            <p class="text-xs text-slate-400 mb-4">Add an extra layer of security to your account.</p>
            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl mb-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-800 dark:text-white">Authenticator App</p>
                  <p class="text-xs text-slate-400">Use Google or Authy authenticator</p>
                </div>
              </div>
              <label class="toggle-switch"><input type="checkbox" checked/><span class="slider"></span></label>
            </div>
            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-800 dark:text-white">Email Verification</p>
                  <p class="text-xs text-slate-400">Receive OTP via email</p>
                </div>
              </div>
              <label class="toggle-switch"><input type="checkbox"/><span class="slider"></span></label>
            </div>
          </div>
        </div>

        <!-- NOTIFICATIONS TAB -->
        <div id="tab-notifications" class="settings-content hidden">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="font-bold text-slate-800 dark:text-white mb-1">Notification Preferences</h2>
            <p class="text-xs text-slate-400 mb-6">Choose when and how to receive notifications.</p>
            <div class="space-y-1">
              <div class="flex items-center justify-between py-4 border-b border-slate-100 dark:border-slate-700">
                <div>
                  <p class="text-sm font-semibold text-slate-800 dark:text-white">New Orders</p>
                  <p class="text-xs text-slate-400 mt-0.5">Get notified when a new order is placed</p>
                </div>
                <label class="toggle-switch"><input type="checkbox" checked/><span class="slider"></span></label>
              </div>
              <div class="flex items-center justify-between py-4 border-b border-slate-100 dark:border-slate-700">
                <div>
                  <p class="text-sm font-semibold text-slate-800 dark:text-white">Payment Received</p>
                  <p class="text-xs text-slate-400 mt-0.5">Alert when a payment is successfully processed</p>
                </div>
                <label class="toggle-switch"><input type="checkbox" checked/><span class="slider"></span></label>
              </div>
              <div class="flex items-center justify-between py-4 border-b border-slate-100 dark:border-slate-700">
                <div>
                  <p class="text-sm font-semibold text-slate-800 dark:text-white">User Registrations</p>
                  <p class="text-xs text-slate-400 mt-0.5">When a new user signs up</p>
                </div>
                <label class="toggle-switch"><input type="checkbox"/><span class="slider"></span></label>
              </div>
              <div class="flex items-center justify-between py-4 border-b border-slate-100 dark:border-slate-700">
                <div>
                  <p class="text-sm font-semibold text-slate-800 dark:text-white">Security Alerts</p>
                  <p class="text-xs text-slate-400 mt-0.5">Suspicious login attempts and security events</p>
                </div>
                <label class="toggle-switch"><input type="checkbox" checked/><span class="slider"></span></label>
              </div>
              <div class="flex items-center justify-between py-4 border-b border-slate-100 dark:border-slate-700">
                <div>
                  <p class="text-sm font-semibold text-slate-800 dark:text-white">Weekly Reports</p>
                  <p class="text-xs text-slate-400 mt-0.5">Summary digest every Monday morning</p>
                </div>
                <label class="toggle-switch"><input type="checkbox" checked/><span class="slider"></span></label>
              </div>
              <div class="flex items-center justify-between py-4">
                <div>
                  <p class="text-sm font-semibold text-slate-800 dark:text-white">Marketing Emails</p>
                  <p class="text-xs text-slate-400 mt-0.5">Product updates and promotional content</p>
                </div>
                <label class="toggle-switch"><input type="checkbox"/><span class="slider"></span></label>
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <button onclick="showSaved()" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Save Preferences</button>
            </div>
          </div>
        </div>

        <!-- APPEARANCE TAB -->
        <div id="tab-appearance" class="settings-content hidden">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 mb-5">
            <h2 class="font-bold text-slate-800 dark:text-white mb-1">Theme</h2>
            <p class="text-xs text-slate-400 mb-5">Choose your preferred color mode.</p>
            <div class="grid grid-cols-3 gap-3">
              <label class="cursor-pointer">
                <input type="radio" name="theme" value="light" class="hidden peer" onchange="setTheme('light')" checked/>
                <div class="peer-checked:ring-2 peer-checked:ring-blue-500 rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-600 peer-checked:border-blue-500 transition-all">
                  <div class="bg-slate-50 p-3">
                    <div class="h-2 bg-white rounded mb-1.5"></div>
                    <div class="h-2 bg-slate-200 rounded w-3/4 mb-1.5"></div>
                    <div class="h-2 bg-slate-200 rounded w-1/2"></div>
                  </div>
                  <div class="px-3 py-2 bg-white text-center text-xs font-semibold text-slate-700">Light</div>
                </div>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="theme" value="dark" class="hidden peer" onchange="setTheme('dark')"/>
                <div class="peer-checked:ring-2 peer-checked:ring-blue-500 rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-600 peer-checked:border-blue-500 transition-all">
                  <div class="bg-slate-900 p-3">
                    <div class="h-2 bg-slate-700 rounded mb-1.5"></div>
                    <div class="h-2 bg-slate-700 rounded w-3/4 mb-1.5"></div>
                    <div class="h-2 bg-slate-700 rounded w-1/2"></div>
                  </div>
                  <div class="px-3 py-2 bg-slate-800 text-center text-xs font-semibold text-slate-200">Dark</div>
                </div>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="theme" value="system" class="hidden peer" onchange="setTheme('system')"/>
                <div class="peer-checked:ring-2 peer-checked:ring-blue-500 rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-600 peer-checked:border-blue-500 transition-all">
                  <div class="p-3" style="background:linear-gradient(135deg, #f8fafc 50%, #0f172a 50%)">
                    <div class="h-2 rounded mb-1.5" style="background:linear-gradient(90deg,#e2e8f0 50%,#334155 50%)"></div>
                    <div class="h-2 rounded w-3/4 mb-1.5" style="background:linear-gradient(90deg,#e2e8f0 50%,#334155 50%)"></div>
                    <div class="h-2 rounded w-1/2" style="background:linear-gradient(90deg,#e2e8f0 50%,#334155 50%)"></div>
                  </div>
                  <div class="px-3 py-2 bg-slate-100 dark:bg-slate-700 text-center text-xs font-semibold text-slate-700 dark:text-slate-200">System</div>
                </div>
              </label>
            </div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="font-bold text-slate-800 dark:text-white mb-1">Language & Region</h2>
            <p class="text-xs text-slate-400 mb-5">Set your language, timezone and date format.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Language</label>
                <select class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                  <option>English (US)</option>
                  <option>Bahasa Indonesia</option>
                  <option>Español</option>
                  <option>Français</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Timezone</label>
                <select class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                  <option>UTC+7 – Jakarta (WIB)</option>
                  <option>UTC+0 – London</option>
                  <option>UTC-5 – New York</option>
                  <option>UTC+9 – Tokyo</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Date Format</label>
                <select class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                  <option>MM/DD/YYYY</option>
                  <option>DD/MM/YYYY</option>
                  <option>YYYY-MM-DD</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Currency</label>
                <select class="w-full px-4 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                  <option>USD ($)</option>
                  <option>IDR (Rp)</option>
                  <option>EUR (€)</option>
                  <option>GBP (£)</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end mt-5">
              <button onclick="showSaved()" class="px-5 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Save Settings</button>
            </div>
          </div>
        </div>

        <!-- INTEGRATIONS TAB -->
        <div id="tab-integrations" class="settings-content hidden">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="font-bold text-slate-800 dark:text-white mb-1">Connected Apps</h2>
            <p class="text-xs text-slate-400 mb-6">Manage third-party service integrations.</p>
            <div class="space-y-3">
              <div class="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                <div class="flex items-center gap-4">
                  <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-sm">Sl</div>
                  <div>
                    <p class="font-semibold text-slate-800 dark:text-white text-sm">Slack</p>
                    <p class="text-xs text-slate-400">Team communication & alerts</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2.5 py-1 rounded-full">Connected</span>
                  <button class="text-xs font-semibold text-red-500 hover:underline">Disconnect</button>
                </div>
              </div>
              <div class="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                <div class="flex items-center gap-4">
                  <div class="w-11 h-11 rounded-xl bg-emerald-500 flex items-center justify-center text-white font-bold text-sm">Gh</div>
                  <div>
                    <p class="font-semibold text-slate-800 dark:text-white text-sm">GitHub</p>
                    <p class="text-xs text-slate-400">Code repository integration</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2.5 py-1 rounded-full">Connected</span>
                  <button class="text-xs font-semibold text-red-500 hover:underline">Disconnect</button>
                </div>
              </div>
              <div class="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                <div class="flex items-center gap-4">
                  <div class="w-11 h-11 rounded-xl bg-violet-600 flex items-center justify-center text-white font-bold text-sm">Jr</div>
                  <div>
                    <p class="font-semibold text-slate-800 dark:text-white text-sm">Jira</p>
                    <p class="text-xs text-slate-400">Project management & tickets</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-2.5 py-1 rounded-full">Not Connected</span>
                  <button class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Connect</button>
                </div>
              </div>
              <div class="flex items-center justify-between p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                <div class="flex items-center gap-4">
                  <div class="w-11 h-11 rounded-xl bg-amber-500 flex items-center justify-center text-white font-bold text-sm">Zp</div>
                  <div>
                    <p class="font-semibold text-slate-800 dark:text-white text-sm">Zapier</p>
                    <p class="text-xs text-slate-400">Workflow automation</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-2.5 py-1 rounded-full">Not Connected</span>
                  <button class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Connect</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- DANGER TAB -->
        <div id="tab-danger" class="settings-content hidden">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-red-200 dark:border-red-800/50 p-6">
            <h2 class="font-bold text-red-600 dark:text-red-400 mb-1">Danger Zone</h2>
            <p class="text-xs text-slate-400 mb-6">These actions are irreversible. Please proceed with extreme caution.</p>
            <div class="space-y-4">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                <div>
                  <p class="font-semibold text-slate-800 dark:text-white text-sm">Export all data</p>
                  <p class="text-xs text-slate-400 mt-0.5">Download a copy of all your account data as ZIP</p>
                </div>
                <button class="px-4 py-2 text-sm font-semibold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">Export Data</button>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-900/10 rounded-xl">
                <div>
                  <p class="font-semibold text-slate-800 dark:text-white text-sm">Deactivate Account</p>
                  <p class="text-xs text-slate-400 mt-0.5">Temporarily disable your account. You can reactivate anytime.</p>
                </div>
                <button class="px-4 py-2 text-sm font-semibold border border-amber-400 text-amber-700 dark:text-amber-400 rounded-xl hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors whitespace-nowrap">Deactivate</button>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/10 rounded-xl">
                <div>
                  <p class="font-semibold text-red-700 dark:text-red-400 text-sm">Delete Account</p>
                  <p class="text-xs text-slate-400 mt-0.5">Permanently delete your account and all associated data. This cannot be undone.</p>
                </div>
                <button onclick="confirmDelete()" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-xl transition-colors whitespace-nowrap">Delete Account</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

<!-- Toast -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden transition-all">
  <div class="flex items-center gap-3 bg-slate-800 dark:bg-white text-white dark:text-slate-800 px-5 py-3 rounded-xl shadow-xl text-sm font-semibold">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    Settings saved successfully!
  </div>
</div>
@endsection

@section('script')
<script>
  function showTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.settings-content').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.settings-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    document.getElementById('nav-' + tab).classList.add('active');
  }

  function setTheme(t) {
    const html = document.documentElement;
    if(t === 'dark') { html.classList.add('dark'); localStorage.setItem('theme','dark'); }
    else if(t === 'light') { html.classList.remove('dark'); localStorage.setItem('theme','light'); }
    else { localStorage.removeItem('theme'); if(window.matchMedia('(prefers-color-scheme: dark)').matches) html.classList.add('dark'); else html.classList.remove('dark'); }
  }

  function checkStrength() {
    const pw = document.getElementById('newpw').value;
    const len = pw.length;
    const bars = ['s1','s2','s3','s4'];
    const colors = ['bg-red-400','bg-amber-400','bg-blue-400','bg-emerald-500'];
    const labels = ['Weak','Fair','Good','Strong'];
    const score = len === 0 ? 0 : len < 6 ? 1 : len < 8 ? 2 : len < 12 ? 3 : 4;
    bars.forEach((id, i) => {
      const el = document.getElementById(id);
      el.className = `h-1.5 flex-1 rounded-full transition-colors ${i < score ? colors[score-1] : 'bg-slate-200 dark:bg-slate-700'}`;
    });
    document.getElementById('strengthLabel').textContent = score > 0 ? labels[score-1] + ' password' : 'Password strength';
    document.getElementById('strengthLabel').className = `text-xs mt-1 ${score===4?'text-emerald-500':score===3?'text-blue-500':score===2?'text-amber-500':score===1?'text-red-500':'text-slate-400'}`;
  }

  function showSaved() {
    const t = document.getElementById('toast');
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 3000);
  }

  function confirmDelete() {
    if(confirm('Are you sure you want to permanently delete your account? This action cannot be undone.')) {
      alert('Account deletion request received. You will receive a confirmation email.');
    }
  }
</script>
@endsection
