<div class="flex items-center justify-center py-4">
    <div
        class="w-36 h-24 rounded-lg bg-gray-200 p-2 flex items-center justify-center transition cursor-pointer"
        {{--
            Every opener declares its own insertion point. This one appends to
            the end, so it clears the target rather than assuming whatever the
            last opener left behind.
        --}}
        x-on:click="window._addBlockBelow = null; $dispatch('open-modal', { id: 'add-block' })"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
             stroke="currentColor" class="w-8 h-8"
        >
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"
            />
        </svg>

        <p class="ml-4">Add Block</p>
    </div>
</div>
