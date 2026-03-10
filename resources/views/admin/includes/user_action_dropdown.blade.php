<div class="relative inline-block text-left" x-data="{ open: false }">
    <button @click="open = !open" @click.away="open = false" class="text-slate-400 hover:text-primary transition-all p-2 rounded-xl hover:bg-slate-50">
        <span class="material-symbols-outlined text-2xl">more_vert</span>
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-56 rounded-[2rem] shadow-xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-[100] p-4 border border-slate-100 overflow-hidden" 
         style="display: none;">
        <div class="space-y-1">
            <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class="group flex items-center px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 rounded-2xl transition-all gap-3 no-underline">
                <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors text-xl">visibility</span>
                View Profile
            </a>
            <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class="group flex items-center px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 rounded-2xl transition-all gap-3 no-underline">
                <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors text-xl">edit</span>
                Edit User
            </a>
            <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit#role" class="group flex items-center px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 rounded-2xl transition-all gap-3 no-underline">
                <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors text-xl">shield</span>
                Change Role
            </a>
            
            <div class="h-px bg-slate-100 my-2 mx-2"></div>
            
            <a href="javascript:void(0)" 
               onclick="confirmDelete('{{ getAdminPanelUrl() }}/users/{{ $user->id }}/delete')"
               class="group flex items-center px-4 py-2.5 text-sm font-bold text-rose-500 hover:bg-rose-50 rounded-2xl transition-all gap-3 no-underline">
                <span class="material-symbols-outlined text-rose-400 group-hover:text-rose-500 transition-colors text-xl">delete</span>
                Delete User
            </a>
        </div>
    </div>
</div>
