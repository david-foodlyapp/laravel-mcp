<?php

namespace App\Livewire\Chat;

use App\Services\OpenAiRestaurantAgent;
use Livewire\Component;

class RestaurantChat extends Component
{
    public string $message = '';

    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public bool $isLoading = false;

    public ?string $error = null;

    public function send(OpenAiRestaurantAgent $agent): void
    {
        $this->validate(['message' => ['required', 'string', 'max:2000']]);

        $userMessage = trim($this->message);
        $this->messages[] = ['role' => 'user', 'content' => $userMessage];
        $this->message = '';
        $this->error = null;
        $this->isLoading = true;

        try {
            $this->messages[] = ['role' => 'assistant', 'content' => $agent->reply($this->messages)];
        } catch (\Throwable $exception) {
            report($exception);
            $this->error = 'AI სერვისთან დაკავშირება ვერ მოხერხდა. გადაამოწმე OPENAI_API_KEY.';
            array_pop($this->messages);
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.chat.restaurant-chat');
    }
}
