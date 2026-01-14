<header class="sticky top-0 bg-white/80 backdrop-blur-md border-b border-slate-200 z-30">
  <div class="px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16 -mb-px">
      <!-- Navbar: Left side -->
      <div class="flex items-center">
        <!-- Hamburger button -->
        <button class="text-slate-500 hover:text-slate-600 lg:hidden" aria-controls="sidebar" aria-expanded="false">
          <span class="sr-only">Open sidebar</span>
          <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <!-- Search input -->
        <div class="hidden sm:block ml-4 relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
          </div>
          <input type="text"
            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-accent focus:border-accent sm:text-sm transition-all"
            placeholder="Quick search...">
        </div>
      </div>

      <!-- Navbar: Right side -->
      <div class="flex items-center space-y-0 ml-4">
        <!-- Company Selector (Placeholder for now) -->
        <div class="mr-4">
          <select class="select2">
            <option>Main Company</option>
            <option>Branch A</option>
            <option>Branch B</option>
          </select>
        </div>

        <!-- Notifications -->
        <button
          class="p-2 text-slate-400 hover:text-slate-500 rounded-full hover:bg-slate-100 transition-colors relative mr-2">
          <i data-lucide="bell" class="w-5 h-5"></i>
          <span class="absolute top-2 right-2.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
        </button>

        <!-- Divider -->
        <hr class="w-px h-6 bg-slate-200 mx-3">

        <!-- User profile -->
        <div class="flex items-center ml-3">
          <div class="flex flex-col text-right mr-3 hidden md:block">
            <span class="text-xs font-semibold text-slate-800 leading-none">John Doe</span>
            <span class="text-[10px] text-slate-500 leading-none mt-1">Administrator</span>
          </div>
          <button
            class="flex text-sm bg-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 transition-all">
            <img class="h-9 w-9 rounded-xl object-cover border-2 border-white shadow-sm"
              src="https://ui-avatars.com/api/?name=John+Doe&background=0f172a&color=fff" alt="">
          </button>
        </div>
      </div>
    </div>
  </div>
</header>
