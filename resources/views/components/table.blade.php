<div {{ $attributes->merge(['class' => 'mt-6 flow-root']) }}>
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl">
                <table class="min-w-full divide-y divide-gray-200">
                    {{ $slot }}
                </table>
            </div>
        </div>
    </div>
</div>
