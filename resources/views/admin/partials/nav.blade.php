<div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex gap-6">
        <a href="{{ route('admin.dashboard') }}" class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ __('Overzicht') }}
        </a>
        <a href="{{ route('admin.feeds') }}" class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium {{ request()->routeIs('admin.feeds') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ __('Feeds') }}
        </a>
        <a href="{{ route('admin.themes') }}" class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium {{ request()->routeIs('admin.themes') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ __('Thema\'s') }}
        </a>
        <a href="{{ route('admin.llm-settings') }}" class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium {{ request()->routeIs('admin.llm-settings') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ __('LLM-instellingen') }}
        </a>
    </nav>
</div>
