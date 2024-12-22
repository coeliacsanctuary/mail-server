<x-mailcoach::modal name="add-block" :dismissable="true">
    <div class="grid grid-cols-3 w-full gap-2" x-data>
        <div class="w-1/4 rounded p-2 bg-gray-200 flex flex-col justify-center items-center"
             x-on:click="$dispatch('add-block', ['single', window._addBlockBelow]); $dispatch('close-modal', {id:'add-block'}); window._addBlockBelow = null;"
        >
            <div class="flex">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-12 h-12"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"
                    />
                </svg>
            </div>

            Single Column
        </div>

        <div class="w-1/4 rounded p-2 bg-gray-200 flex flex-col justify-center items-center"
             x-on:click="$dispatch('add-block', ['double', window._addBlockBelow]); $dispatch('close-modal', {id:'add-block'}); window._addBlockBelow = null;"
        >
            <div class="flex">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-12 h-12"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"
                    />
                </svg>

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-12 h-12"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"
                    />
                </svg>
            </div>

            Double Column
        </div>

        <div class="w-1/4 rounded p-2 bg-gray-200 flex flex-col justify-center items-center"
             x-on:click="$dispatch('add-block', ['triple', window._addBlockBelow]); $dispatch('close-modal', {id:'add-block'}); window._addBlockBelow = null;"
        >
            <div class="flex">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-8 h-8"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"
                    />
                </svg>

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-8 h-8"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"
                    />
                </svg>

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-8 h-8"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"
                    />
                </svg>
            </div>

            Triple Column
        </div>
    </div>
</x-mailcoach::modal>
