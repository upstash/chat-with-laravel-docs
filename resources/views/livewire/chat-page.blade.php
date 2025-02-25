<div class="h-full flex flex-col">
    <div class="px-6 pt-6 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <span>Built using</span>
            <x-icons.vector class="w-8 h-8" />
            <span>Upstash Vector</span>
        </div>
        <div class="flex items-center space-x-2">
            <span>[GitHub icon here]
        </div>
    </div>
    <div class="flex flex-col lg:flex-row p-6 flex-1 h-full max-h-full gap-4">
        <section class="flex-1 flex flex-col border rounded-lg bg-white shadow-lg h-full">
            <div class="border-b p-6">
                <h1 class="font-semibold">Chat</h1>
                <p class="text-sm opacity-50">Ask a question to the Laravel 12.x documentation</p>
            </div>
            <div class="p-6 flex-1 overflow-scroll space-y-6">
                @foreach ($chat as $message)
                    @if ($message['role'] === 'user')
                        <div class="flex justify-end ml-4">
                            <div>
                                <div class="text-xs p-1">You</div>
                                <div class="rounded-lg bg-blue-600 text-white px-3 py-2 text-sm whitespace-pre-wrap">{{ $message['content'] }}</div>
                            </div>
                        </div>
                    @elseif ($message['role'] === 'assistant')
                        <div class="flex mr-4">
                            <div>
                                <div class="text-xs p-1">Assistant</div>
                                <div class="rounded-lg bg-gray-100 px-3 py-2 text-sm whitespace-pre-wrap">{{ $message['content'] }}</div>
                                @if (isset($message['sources']) && count($message['sources']) > 0)
                                <div class="pt-4">
                                    <h3 class="pb-2 text-xs">Sources</h3> 
                                    <ul class="flex flex-col space-y-3">
                                        @foreach ($message['sources'] as $source)
                                            <li><a class="text-xs rounded-lg bg-gray-100 px-2 py-2" href="{{ $source }}">{{ $source }}</a></li>
                                        @endforeach
                                    </ul>
                                </div> 
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
                @if($isChatLoading)
                <div class="flex mr-4">
                    <div>
                        <div class="text-xs p-1">Assistant</div>
                        <div class="rounded-lg bg-gray-100 px-3 py-2 text-sm whitespace-pre-wrap" wire:stream="answer">Thinking...</div>
                    </div>
                </div>
                @endif
            </div>
            <div class="p-6 border-t">
                <form wire:submit="askQuestion">
                    <input autofocus wire:model="question" type="text" placeholder="Ask your question" class="border rounded-lg w-full py-2 px-3">
                    <div class="flex justify-end pt-4">
                        <button class="border rounded-full px-4 py-1.5">Ask Question</button>
                    </div>
                </form>
            </div>
        </section>
        <section class="flex-1 flex flex-col border rounded-lg bg-white shadow-lg h-full">
            <div class="border-b p-6">
                <h1 class="font-semibold">Debug Context</h1>
                <p class="text-sm opacity-50">See what parts of the documentation were fetched from the vector database</p>
            </div>
            <div class="overflow-y-scroll flex-1 flex flex-col space-y-6 divide-y">
                @foreach ($context as $item)
                    <div class="p-6">
                        <x-markdown class="markdown">
                        {{ $item['text'] }}
                        </x-markdown>
                        <div class="pt-6">
                            <h3 class="font-semibold pb-2 text-sm">Sources</h3>
                            <ul>
                                @foreach ($item['sources'] as $source)
                                    <li><a class="text-xs rounded-lg bg-gray-50 px-2 py-1" href="{{ $source }}">{{ $source }}</a></li>
                                @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
