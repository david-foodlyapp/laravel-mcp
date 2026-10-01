<div class="mx-auto flex min-h-[calc(100vh-8rem)] max-w-4xl flex-col gap-6">
    <div>
        <flux:heading size="xl">Restaurant AI</flux:heading>
        <flux:subheading>ჰკითხე აგენტს Foodly-ის რესტორნების შესახებ.</flux:subheading>
    </div>

    <div class="flex-1 space-y-4 overflow-y-auto rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        @forelse ($messages as $index => $chatMessage)
            <div wire:key="message-{{ $index }}" class="flex {{ $chatMessage['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[85%] rounded-2xl px-4 py-3 {{ $chatMessage['role'] === 'user' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' }}">
                    <div class="mb-1 text-xs font-medium opacity-60">{{ $chatMessage['role'] === 'user' ? 'You' : 'Restaurant AI' }}</div>
                    <div class="whitespace-pre-wrap text-sm">{{ $chatMessage['content'] }}</div>
                </div>
            </div>
        @empty
            <div class="flex h-full min-h-64 items-center justify-center text-center text-sm text-zinc-500">მაგალითად: „მაჩვენე ბათუმის რესტორნები“</div>
        @endforelse

        <div wire:loading wire:target="send" class="text-sm text-zinc-500">Restaurant AI ფიქრობს…</div>
    </div>

    @if ($error)
        <flux:callout variant="danger">{{ $error }}</flux:callout>
    @endif

    <form wire:submit="send" class="flex items-end gap-3">
        <flux:textarea wire:model="message" placeholder="დაწერე კითხვა რესტორნების შესახებ…" rows="2" class="flex-1" :disabled="$isLoading" />
        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="send">გაგზავნა</flux:button>
    </form>
</div>
