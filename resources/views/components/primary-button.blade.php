<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-ink hover:bg-neutral-800 border border-transparent rounded-xl font-semibold text-sm text-white shadow-sm hover:shadow transition duration-150 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>