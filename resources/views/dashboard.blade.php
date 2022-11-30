<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Papers') }} <span class="text-gray-400">({{ $papers->count() }})</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                @forelse ($papers as $paper)
                    <papers class="space-y-1">
                        <h2 class="font-semibold text-2xl">{{ $paper->title }}</h2>

                        <p class="m-0">{{ $paper->body }}</body>

                        <div>
                            @foreach ($paper->keywords as $keyword)
                                <span class="text-xs px-2 py-1 rounded bg-indigo-50 text-indigo-500">{{ $keyword}}</span>
                            @endforeach
                        </div>
                    </papers>
                @empty
                    <p>No papers found</p>
                @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
