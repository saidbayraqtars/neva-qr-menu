<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn bg-red-600 text-white hover:bg-red-500 active:scale-[.98]']) }}>
    {{ $slot }}
</button>
