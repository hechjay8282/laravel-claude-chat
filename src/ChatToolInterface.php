<?php

namespace Hakim\ClaudeChat;

interface ChatToolInterface
{
    /** Returns the Anthropic tool definitions array */
    public function getTools(): array;

    /** Executes a tool call and returns the result */
    public function handleToolCall(string $name, array $input): array;
}
