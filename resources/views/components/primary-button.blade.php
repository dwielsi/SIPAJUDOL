<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 active:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
