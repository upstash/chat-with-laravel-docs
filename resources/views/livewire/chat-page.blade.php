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
        <section class="flex-1 flex flex-col border dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 shadow-lg dark:shadow-none h-full">
            <div class="border-b dark:border-slate-700 p-6">
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
                                <div class="rounded-lg bg-gray-100 dark:bg-slate-600 px-3 py-2 text-sm whitespace-pre-wrap">{{ $message['content'] }}</div>
                                @if (isset($message['sources']) && count($message['sources']) > 0)
                                <div class="pt-4">
                                    <h3 class="pb-2 text-xs">Sources</h3> 
                                    <ul class="flex flex-col space-y-3">
                                        @foreach ($message['sources'] as $source)
                                            <li><a class="text-xs rounded-lg bg-gray-100 dark:bg-slate-600 px-2 py-2" href="{{ $source }}">{{ $source }}</a></li>
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
                        <div class="rounded-lg bg-gray-100 dark:bg-slate-600 px-3 py-2 text-sm whitespace-pre-wrap" wire:stream="answer">Thinking...</div>
                    </div>
                </div>
                @endif
            </div>
            <div class="p-6 border-t dark:border-slate-700">
                <form wire:submit="askQuestion">
                    <input autofocus wire:model="question" type="text" placeholder="Write a question here and we will try to figure how the best way to answer that." class="border bg-white dark:bg-slate-700 dark:border-slate-600 rounded-lg w-full py-2 px-3 focus:ring focus:ring-orange-500 focus:outline-none">
                    <div class="flex justify-end pt-4">
                        <button class="border bg-gray-50 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-700 dark:hover:bg-slate-600 focus:ring focus:ring-orange-500 focus:outline-none rounded-lg px-4 py-1.5">Send Question</button>
                    </div>
                </form>
            </div>
        </section>
        <section class="flex-1 flex flex-col border dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 shadow-lg h-full">
            <div class="border-b dark:border-slate-700 p-6 flex justify-between items-center">
                <div>
                <h1 class="font-semibold">Debug Context</h1>
                <p class="text-sm opacity-50">See what parts of the documentation were fetched from our vector database</p>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" wire:model="useReranker" class="rounded-lg border-gray-300 dark:border-slate-600 accent-orange-600">
                    <label class="ml-2 text-sm dark:text-slate-400">Use Reranker</label>
                </div>
            </div>
            <div class="overflow-y-scroll flex-1 flex flex-col space-y-6 divide-y dark:divide-slate-700">
                @foreach ($context as $item)
                    <div class="p-6">
                        <x-markdown class="markdown">
                        {{ $item['text'] }}
                        </x-markdown>
                        <div class="pt-6">
                            <h3 class="font-semibold pb-2 text-sm">Sources</h3>
                            <ul>
                                @foreach ($item['sources'] as $source)
                                    <li><a class="text-xs rounded-lg bg-gray-50 dark:bg-slate-600 px-2 py-1" href="{{ $source }}">{{ $source }}</a></li>
                                @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
