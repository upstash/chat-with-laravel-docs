<main class="flex flex-col">

    <!-- HEADER -->
    <header class="px-4 lg:px-6 h-[60px] flex justify-between items-center lg:items-end">
        <div class="flex items-center gap-1">
            <span>Powered by</span>
            <x-icons.vector class="size-5" />
            <a href="https://upstash.com" target="_blank">Upstash Vector</span>
        </div>
        <div class="flex items-center space-x-2">
            <a aria-label="GitHub" href="https://github.com/upstash/chat-with-laravel-docs/" target="_blank" class="opacity-50 hover:opacity-100 p-2">
                <x-icons.github class="size-6" />
            </a>
        </div>
    </header>

    <!-- MAIN -->
    <div class="lg:h-[calc(100vh-60px)] px-4 lg:p-6 grow grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">

        <!-- CHAT -->
        <section class="lg:h-full overflow-hidden grid grid-rows-[auto_1fr_auto] border dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 shadow-md dark:shadow-none">
            <div class="border-b dark:border-slate-700 p-6">
                <h1 class="font-semibold">Chat</h1>
                <p class="text-sm opacity-50">Ask a question to the Laravel 12.x documentation</p>
            </div>

            <div class="p-6 space-y-6 overflow-y-scroll min-h-[200px] max-h-[400px] lg:min-h-full lg:max-h-auto">
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
                                <div class="rounded-lg bg-gray-100 dark:bg-slate-600 px-3 py-2 text-sm markdown">{!! $message['content'] !!}</div>
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
                        <div class="rounded-lg bg-gray-100 dark:bg-slate-600 px-3 py-2 text-sm markdown" wire:stream="answer">Thinking...</div>
                    </div>
                </div>
                @endif
            </div>

            <div class="p-6 border-t dark:border-slate-700">
                <form wire:submit="askQuestion" class="flex items-center gap-4">
                    <input wire:loading.attr="disabled" autofocus wire:model="question" type="text" placeholder="Write a question here and we will try to figure how the best way to answer that."
                    class="border bg-white dark:bg-slate-700 dark:border-slate-600 rounded-lg w-full py-2 px-3 focus:ring focus:ring-orange-500 focus:outline-none">
                        <button wire:loading.attr="disabled"
                        class="py-2 px-3 border bg-gray-50 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-700 dark:hover:bg-slate-600 focus:ring focus:ring-orange-500 focus:outline-none rounded-lg">
                        Send</button>
                </form>
            </div>
        </section>

        <!-- DEBUG -->
        <section class="lg:h-full overflow-hidden flex flex-col border dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 shadow-md">
            <div class="relative border-b dark:border-slate-700 p-6">
                <h1 class="font-semibold">Debug Context</h1>
                <p class="text-sm opacity-50">See what parts of the documentation were fetched from our vector database</p>
                <label class="absolute right-6 top-6 flex items-center gap-1 text-sm dark:text-slate-400">
                    <input type="checkbox" wire:model="useReranker" class="border-gray-300 dark:border-slate-600 accent-orange-600">
                    Use Reranker
                </label>
            </div>

            <div class="overflow-y-scroll grow flex flex-col divide-y dark:divide-slate-700">
                @foreach ($context as $item)
                    <div class="p-6">
                        <x-markdown class="markdown" :highlight-code="false">{!! $item['text'] !!}</x-markdown>
                        <div class="pt-6">
                            <h3 class="font-semibold pb-2 text-xs opacity-50 uppercase">Sources</h3>
                            <ul>
                                @foreach ($item['sources'] as $source)
                                    <li><a class="rounded-lg dark:bg-slate-600 underline" href="{{ $source }}">{{ $source }}</a></li>
                                @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</main>
